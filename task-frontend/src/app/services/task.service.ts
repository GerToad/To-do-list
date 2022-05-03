import { Injectable } from "@angular/core";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Observable } from "rxjs";
import { Task } from "../models/task";
import { global } from "./global";

@Injectable()
export class TaskService {
	public url: string;
	public identity: any;
	public token: any;

	constructor(
		public _http: HttpClient
	){
		this.url = global.url;
	}

	create(token: string, task: Task): Observable<any>{
		let json = JSON.stringify(task);
		let params = "json="+json;

		let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded')
																	 .set('Authorization', token);

		return this._http.post(this.url+'create', params, {headers: headers});
	}

	tasks(token: string, page: any): Observable<any>{
		if(!page){
			page = 1;
		}

		let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded')
																	 .set('Authorization', token);

		return this._http.get(this.url+'tasks?page='+page, {headers: headers});
	}

	task(token: string, id: any): Observable<any>{
		let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded')
																	 .set('Authorization', token);

		return this._http.get(this.url+'task/'+id, {headers: headers});
	}

	update(token: string, task: any): Observable<any>{
		let json = JSON.stringify(task);
		let params = "json="+json;

		let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded')
																	 .set('Authorization', token);

		return this._http.put(this.url+'task/update/'+task.id, params, {headers: headers});
	}

	check(token: string, task: any): Observable<any>{
		let json = JSON.stringify(task);
		let params = "check="+json;

		let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded')
																	 .set('Authorization', token);

		return this._http.put(this.url+'check/'+task.id, params, {headers: headers});
	}

	search(token: string, search: string):Observable<any>{
		let headers = new HttpHeaders().set('Content-Type', 'application/x-www-form-urlencoded')
																	 .set('Authorization', token);

		return this._http.get(this.url+'task/search/'+search, {headers: headers});
	}

}
