<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppController extends Controller
{
    // fields query is a generated numeric field value by ip-api
    protected $ip_api_url = "http://ip-api.com/json/%s?fields=49360";
    protected $weather_api_url = "https://api.open-meteo.com/v1/forecast?latitude=%s&longitude=%s&current=temperature_2m";

    public function __invoke(Request $request)
    {
        $ip = $request->header("Fly-Client-IP", $request->ip());
        $visitor["name"] = $request->input("visitor_name");

        // random ip for debugging, not mine
        // $ip = "102.80.23.161";

        // try get location from ip-api
        try {
            $resp = Http::get(sprintf($this->ip_api_url, $ip));
            if ($resp->ok()) {
                $data = $resp->json();
                if ($data["status"] != "success") {
                    throw new Exception($data["message"]);
                }

                $visitor["city"] = $data["city"];
                $visitor["lat"] = $data["lat"];
                $visitor["lon"] = $data["lon"];
            }
        } catch (Exception $e) {
            return [
                "status" => "error",
                "message" => "Error getting ip info: " . $e->getMessage(),
            ];
        }

        // try get weather info
        try {
            $resp = Http::get(
                sprintf(
                    $this->weather_api_url,
                    $visitor["lat"],
                    $visitor["lon"]
                )
            );
            if ($resp->ok()) {
                $data = $resp->json();
                $visitor["temp"] = $data["current"]["temperature_2m"];
            }
        } catch (Exception $e) {
            return [
                "status" => "error",
                "message" => "Error getting ip info: " . $e->getMessage(),
            ];
        }

        return [
            "client_ip" => $ip,
            "location" => $visitor["city"],
            "greeting" => "Hello, {$visitor["name"]}!, the temperature is {$visitor["temp"]} degrees celcius in {$visitor["city"]}",
        ];
    }
}
