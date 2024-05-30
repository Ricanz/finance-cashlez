<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Http\Controllers\RestController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeneralController extends RestController
{

    public function portalLogin(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();

            return RestController::sendResponse(null, "Berhasill Login");
        } catch (\Throwable $th) {
            return RestController::sendError(null, "Gagal Login");
        }
    }

    public function check(Request $request)
    {
        $upload = Utils::uploadImageOri($request->file);
        return RestController::sendResponse(null, $upload);
    }

    public function upload(Request $request)
    {
        $upload = Utils::uploadImageOri($request->file);
        return RestController::sendResponse(null, $upload);
    }

    public function test()
    {
        dd("hi");
    }

    public function store(Request $request)
    {
        $this->readAndMapCSV($request->file);
    }


    public function readAndMapCSV($url)
    {
        // Retrieve the CSV file from the URL
        $response = Http::get($url);

        // Check if the request was successful
        if ($response->successful()) {
            // Parse the CSV content
            $csvData = $response->body();
            $rows = explode("\n", $csvData); // Split rows

            // Initialize an empty collection to store the mapped data
            $mappedData = collect();

            // Map each field
            foreach ($rows as $row) {
                dd($row);
                $fields = str_getcsv($row); // Parse the CSV row

                // Assuming the first row contains the headers
                // You can use this information to map fields
                if ($rows[0] === $row) {
                    $headers = $fields;
                    continue;
                }

                // Initialize an empty array to store mapped row data
                $mappedRow = [];

                // Map each field based on its position
                foreach ($fields as $index => $field) {
                    $header = $headers[$index] ?? null; // Get the header for the current field
                    if ($header) {
                        // Map the field using the header
                        $mappedRow[$header] = $field;
                    }
                }

                // Add mapped row to the collection
                $mappedData->push($mappedRow);
            }

            // Now $mappedData contains the CSV data with each field mapped according to the headers
            // You can further process or return this data as needed
            return $mappedData;
        } else {
            // Handle the case where the request failed
            return response()->json(['error' => 'Failed to fetch CSV file'], 500);
        }
    }
}
