<?php

namespace Tests\Feature;

use App\Models\Postcode;
use App\Models\Store;
use Tests\TestCase;

class StoreQueryPerformanceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testStoreQueryPerformance(): void
    {
        // Haversine formula expression with placeholders.
        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(`lat`)) * cos(radians(`long`) - radians(?)) + sin(radians(?)) * sin(radians(`lat`))))";

        // Total number of postcode rows.
        $totalPostcodes = Postcode::count();

        // Use an environment variable to determine the number of tests (default to 100 if not provided).
        $numberOfTests = (int) env('NUMBER_OF_TESTS', 100);
        // Compute the step as the total rows divided by the number of tests.
        $step = (int) ceil($totalPostcodes / $numberOfTests);

        // Accumulators for total execution time for each query version.
        $testsExecuted   = 0;
        $totalTimeWhere  = 0;
        $totalTimeHaving = 0;

        // Loop over the postcode records in steps.
        for ($id = 1; $id <= $totalPostcodes; $id += $step) {
            // Retrieve the postcode record by its unique ID.
            $postcodeRecord = Postcode::find($id);
            if (!$postcodeRecord) {
                continue; // Skip if no record exists for this ID.
            }

            $lat  = $postcodeRecord->lat;
            $long = $postcodeRecord->long;

            // --- Query Version 1: Using WHERE clause ---
            $startWhere = microtime(true);
            Store::select()
                ->selectRaw("$haversine as distance", [$lat, $long, $lat])
                ->whereRaw("$haversine <= max_delivery_distance", [$lat, $long, $lat])
                ->orderBy('distance', 'asc')
                ->paginate(20);
            $endWhere = microtime(true);
            $totalTimeWhere += ($endWhere - $startWhere);

            // --- Query Version 2: Using HAVING clause (with GROUP BY) ---
            $startHaving = microtime(true);
            Store::selectRaw("*, $haversine as distance", [$lat, $long, $lat])
                ->groupBy('stores.id')
                ->havingRaw("distance <= max_delivery_distance")
                ->orderBy('distance', 'asc')
                ->paginate(20);
            $endHaving = microtime(true);
            $totalTimeHaving += ($endHaving - $startHaving);

            $testsExecuted++;
        }

        // Compute average times for each query version.
        $averageTimeWhere  = $testsExecuted ? $totalTimeWhere / $testsExecuted : 0;
        $averageTimeHaving = $testsExecuted ? $totalTimeHaving / $testsExecuted : 0;

        echo "\nNumber of tests executed: {$testsExecuted}\n";
        echo "\nAverage time for WHERE version: {$averageTimeWhere} seconds\n";
        echo "\nAverage time for HAVING version: {$averageTimeHaving} seconds\n";

        // Determine the winner: the query version with an average time under 1 second.
        $winner = '';
        if ($averageTimeWhere < 1.0 || $averageTimeHaving < 1.0) {
            if ($averageTimeWhere <= $averageTimeHaving) {
                $winner = "WHERE version (Average: {$averageTimeWhere} seconds)\n";
            } else {
                $winner = "HAVING version (Average: {$averageTimeHaving} seconds)\n";
            }
        }

        echo "\nWinner: " . ($winner ?: "None of the queries averaged under 1 second.\n");

        // Assert that at least one query version averaged under 1 second.
        $this->assertNotEmpty($winner, "No query version performed under 1 second on average.\n");
    }
}
