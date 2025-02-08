<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Exception;


class ImportPostcodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:postcodes {csv_file} {number_of_rows} {zip_url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a specified number of rows from a CSV file into the database.';

    /**
     * The extended help message for the command.
     *
     * @var string
     */
    protected $help = <<<EOT
This command imports a specified number of rows from a CSV file into the database.
It can optionally download and extract a ZIP file containing the CSV before importing.

Usage:
  php artisan import:postcodes {csv_file} {number_of_rows} {zip_url?}

Arguments:
  csv_file          The path to the CSV file to import.
                    The file must include the following columns: 'pcd', 'lat', 'long'.
  number_of_rows    The number of rows to import from the CSV file.
                    Use 0 (zero) to import all rows.
  zip_url           (Optional) A URL to a ZIP file containing the CSV file.
                    If provided, the command will download and extract the ZIP file before processing.

Example:
   php artisan import:postcodes Data/ONSPD_NOV_2022_UK.csv 0 https://parlvid.mysociety.org/os/ONSPD/2022-11.zip

Make sure you know the folder structure of the unzipped archive so you can correctly set the csv_file path.
EOT;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the arguments
        $csv_file = $this->argument('csv_file');
        $number_of_rows = $this->argument('number_of_rows');
        $zip_url = $this->argument('zip_url');

        if ((int)$number_of_rows === 0) {
            $number_of_rows = PHP_INT_MAX;
        } else {
            $number_of_rows = (int)$number_of_rows;
        }

        $tmp_dir = storage_path('tmp');

        // If a ZIP URL is provided, download and unzip the file.
        if ($zip_url) {
            if (!is_dir($tmp_dir)) {
                mkdir($tmp_dir, 0755, true);
            }

            $this->info('Downloading ZIP file...');
            $zip_file = $tmp_dir . '/postcodes.zip';
            file_put_contents($zip_file, fopen($zip_url, 'r'));

            $this->info('Extracting ZIP file...');
            $zip = new ZipArchive;
            $zip->open($zip_file);
            $zip->extractTo($tmp_dir);
            $zip->close();

            $this->info('Removing ZIP file...');
            unlink($zip_file);
        }

        $csv_file = $tmp_dir . DIRECTORY_SEPARATOR . $csv_file;

        if (!file_exists($csv_file)) {
            $this->error('CSV file not found at: {$csv_file}');
            return 1;
        }

        // Open the CSV file.
        if (($handle = fopen($csv_file, 'r')) === false) {
            $this->error('Failed to open CSV file.');
            return 1;
        }

        // Read the CSV file.
        $header = fgetcsv($handle);
        if (!$header) {
            $this->error('Failed to read CSV header.');
            return 1;
        }

        // Normalize header: trim spaces and convert to lowercase.
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);

        // Validate that required columns exist.
        $required = ['pcd', 'lat', 'long'];
        $missing  = array_diff($required, $header);
        if (!empty($missing)) {
            $this->error('The CSV file is missing required columns: ' . implode(', ', $missing));
            fclose($handle);
            return 1;
        }

        // Determine the column indexes for required fields.
        $indexPcd  = array_search('pcd', $header);
        $indexLat  = array_search('lat', $header);
        $indexLong = array_search('long', $header);

        // Truncate the table before importing.
        DB::table('postcodes')->truncate();
        $this->info('Truncated the postcodes table.');

        // Import the data.
        $imported = 0;
        $this->info('Importing data...');
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false && $imported < $number_of_rows) {
                // Skip empty rows.
                if (empty($row) || count($row) < count($header)) {
                    continue;
                }

                $pcd  = $row[$indexPcd]  ?? null;
                $lat  = $row[$indexLat]  ?? null;
                $long = $row[$indexLong] ?? null;

                if (!$pcd || !$lat || !$long) {
                    $this->warn('Skipping a row due to missing required data.');
                    continue;
                }

                DB::table('postcodes')->insert([
                    'pcd'        => $pcd,
                    'lat'        => $lat,
                    'long'       => $long,
                ]);

                $imported++;
            }
            DB::commit();
            $this->info("Successfully imported $imported rows.");
        } catch (Exception $e) {
            DB::rollBack();
            $this->error('An error occurred during the import: ' . $e->getMessage());
            fclose($handle);
            return 1;
        }

        fclose($handle);
        exit(0);
    }
}
