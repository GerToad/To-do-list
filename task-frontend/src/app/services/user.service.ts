import { Injectable } from "@angular/core";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Observable } from "rxjs";
import { User } from "../models/user";
import { global } from "./global";

@Injectable()
export class UserService {
	public url: string;
	public identity: any;
	public token: any;

	constructor(
		public _http: HttpClient
	){
		this.url = global.url;
	}

	test(){
		return "Hallo welt";
	}

	register(user: User):Observable<any>{
		let json = JSON.stringify(user);
		let params = 'json='+json;

		let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');

		return this._http.post(this.url+'register', params, {headers: headers});
	}

	login(user: any, getToken = false):Observable<any>{

		if(getToken){
			user.getToken = 'true';
		}
		let json = JSON.stringify(user);
		let params = 'json='+json;

		let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded');

		return this._http.post(this.url+'login', params, {headers: headers});
	}

	update(user: User):Observable<any>{
		let json = JSON.stringify(user);
		let params = 'json='+json;

		let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded')
																	.set('Authorization', this.getToken());

		return this._http.put(this.url+'update', params, {headers: headers});
	}

	getIdentity(){
		let identity = JSON.parse(localStorage.getItem('identity') as string);

		if(identity && identity != undefined){
			this.identity = identity;
		}else{
			this.identity = null;
		}

		return this.identity;
	}

	getToken(){
		let token = localStorage.getItem('token');

		if(token && token != undefined){
			this.token = token;
		}else{
			this.token = null;
		}

		return this.token;
	}

}
