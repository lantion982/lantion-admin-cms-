<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class PassportCustomProviderAccessToken{
    private $server;

    public function __construct(ResourceServer $server){
        $this->server = $server;
    }

    public function handle($request,Closure $next){
	    //log::info('Passport befor=>Request:'.json_encode($request));
        $psr = (new DiactorosFactory())->createRequest($request);
        try{
            $psr = $this->server->validateAuthenticatedRequest($psr);
            $token_id = $psr->getAttribute('oauth_access_token_id');
            if($token_id){
                $access_token = DB::table('oauth_access_token_providers')->where('oauth_access_token_id',$token_id)
                    ->first();
                if($access_token){
                    \Config::set('auth.guards.api.provider',$access_token->provider);
                }
            }
        }catch(\Exception $e){
	        //log::info('Passporterror=>Request:'.json_encode($request));
	        //Log::error('Passporterror=>'.$e->getFile());
			//Log::info('Passporterror=>'.$e->getMessage());
	        //return $next($request);
        }
        return $next($request);
    }
}
