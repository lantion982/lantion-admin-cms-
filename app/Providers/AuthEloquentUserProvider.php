<?php

namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class AuthEloquentUserProvider extends EloquentUserProvider{

	public function retrieveById($identifier){
		return $this->createModel()->newQuery()->find($identifier);
	}

	public function retrieveByCredentials(array $credentials){
		//如果找到了用户，则在Guard实例使用
		$query = $this->createModel()->newQuery();
		foreach($credentials as $key => $value){
			if(!Str::contains($key,'password')){
				$query->where($key,$value);
			}
		}

		return $query->first();
	}

	//根据给定的凭据验证用户
	public function validateCredentials(Authenticatable $user,array $credentials){
		$plain = $credentials['password'];
		return $this->hasher->check($plain,$user->getAuthPassword());
	}

}