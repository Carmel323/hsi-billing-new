<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Sandstone
{
    protected $baseUrl;
    protected $userId;
    protected $apiToken;

    public function __construct()
    {
        $this->baseUrl = config('services.sandstone.base_url');
        $this->userId = config('services.sandstone.user_id');
        $this->apiToken = config('services.sandstone.api_token');
    }

    private function normalizeProviderNameForRedis($providerName)
    {
        return strtolower(preg_replace("/\s/","-",preg_replace("/[^a-zA-Z0-9\s\-]/", '', $providerName)));
    }

    /**
     * Get all provider names and their matching slugs from sandstone.
     *
     * @param string $siteGroup
     * @return array
     * @throws \Exception
     */
    public function getProviderSlug($providerName)
    {
        $redisProviderName = $this->normalizeProviderNameForRedis($providerName);
        // If provider name is in redis cache, return the slug
        if ($slug = Redis::get("sandstone-provider:{$redisProviderName}")) {
            return $slug;
        } else {
            // Otherwise, fetch all providers to get a fresh list of providers and store it in the cache
            $this->fetchAllProviders();

            // Check if the provider name is in the cache again if its not then just return null
            if ($slug = Redis::get("sandstone-provider:{$redisProviderName}")) {
                return $slug;
            }
        }
        return null;
    }

    /**
     * Get all provider names and their matching slugs from sandstone.
     *
     * @param string $siteGroup
     * @return array
     * @throws \Exception
     */
    public function fetchAllProviders($siteGroup = "HSI")
    {
      // Define the GraphQL endpoint
      $endpoint = "{$this->baseUrl}/graphql";

      // Define the query
      $query = [
          'query' => "{
              getAllProviders(siteGroup: {$siteGroup}) {
                  name
                  slug
              }
          }"
      ];

      try {
          // Make the HTTP POST request to the GraphQL API
          $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
          ])->post($endpoint, $query);

          // Handle the response
          if ($response->successful()) {
              
            $providers = $response->json();

            // Store the providers in the cache for future provider slug requests
            if (isset($providers['data']['getAllProviders'])){
              foreach ($providers['data']['getAllProviders'] as $provider) {
                      Redis::set("sandstone-provider:" . $this->normalizeProviderNameForRedis($provider['name']), $provider['slug']);
              }
            }
            
            return $providers['data']['getAllProviders'];

          } else { // Handle errors if the request was not successful
              
            // Log the error response details
            Log::error('Sandstone GraphQL API request failed', [
              'status' => $response->status(),
              'body' => $response->body(),
            ]);

            return $response->body();
          }

      } catch (\Exception $e) {

        // Log any request-specific exceptions
        Log::error('GraphQL API request exception', [
          'message' => $e->getMessage(),
          'trace' => $e->getTraceAsString(),
        ]);

        return $e->getMessage();
      }

    }

    /**
     * send presigned s3 URL notification to the /local-isp/upload/{providerSlug} endpoint.
     *
     * @param string $providerSlug
     * @param string $presignedUrl
     * @return \Illuminate\Http\Client\Response
     */
    public function AOAFileUploadNotification($companyName, $aoaFileId, $presignedUrl, $preview = false)
    {
      // convert the preview boolean to a string
      $preview = $preview ? 'true' : 'false';
      if(strtolower(env("APP_ENV")) != "production") {
        $preview = 'true';
      }

      // Get the provider slug if it exists then proceed to send the notification to sandstone to download the AOA file
      if ($providerSlug = $this->getProviderSlug($companyName)) {
        
        // Define the endpoint specific to the provider slug
        $endpoint = "{$this->baseUrl}/local-isp/upload/{$providerSlug}?preview={$preview}";

        // Prepare the data for the POST request
        $data = [
            'userId' => $this->userId,
            'fileId' => $aoaFileId,
            'url' => $presignedUrl,
        ];
        
        // Make the POST request with the authorization header
        try {
          
          // send the notification request to sandstone that there is a new file uploaded
          $response = Http::withToken($this->apiToken)->post($endpoint, $data);
          
          if ($response->successful()) {
            return $response;
          } else {
            Log::error('Sandstone AOA File Upload Notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
          }

        } catch (\Exception $e) {
          Log::error('Sandstone AOA File Upload Notification failed', [
              'message' => $e->getMessage(),
              'trace' => $e->getTraceAsString(),
          ]);
        }

      } else {
          // If the provider slug is not found, log an error and return false
          Log::error('Provider slug not found, Cannot send AOA upload notification to Sandstone without a slug', [
              'companyName' => $companyName,
          ]);
      }
      return;
    }

}