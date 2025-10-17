<?php

namespace App\Http\Middleware;

use App\Libs\Helper;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class RefreshTokenMiddleware{

    public function handle($request,Closure $next){
        $http = new \GuzzleHttp\Client();
        $accessToken = '';
        $refreshToken = '';
        $responseReturn = $next($request);

        if($request->headers->get('refreshToken') != ''){
            try{
	            $api_url = config('passport.api_url');
                $response = $http->post($api_url,[
                    'form_params' => [
                        'grant_type'    => 'refresh_token',
                        'refresh_token' => $request->headers->get('refreshToken'),
                        'client_id'     => config('passport.client_id'),
                        'client_secret' => config('passport.client_secret'),
                        'scope'         => '',
                    ],
                ]);
                $accessToken  = Arr::get(json_decode((string)$response->getBody(),true),'access_token');
                $refreshToken = Arr::get(json_decode((string)$response->getBody(),true),'refresh_token');
            }catch(\GuzzleHttp\Exception\ClientException $e){
                Log::error($e->getMessage());
            }
            $responseReturn->headers->set('Authorization',$accessToken);
            $responseReturn->headers->set('refreshToken',$refreshToken);
        }

        $responseReturn->headers->set('singleToken',$request->headers->get('singleToken'));

        return $responseReturn;
    }
}
