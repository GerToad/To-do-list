import { Injectable } from "@angular/core";
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, UrlTree } from "@angular/router";
import {Observable} from "rxjs";
import { UserService } from "./user.service";

@Injectable()
export class IdentityGuard implements CanActivate{

	constructor(
		private _router: Router,
		private _userService: UserService
	){}

	canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean | UrlTree | Observable<boolean | UrlTree> | Promise<boolean | UrlTree> {
		let identity = this._userService.getIdentity();

		if(identity && identity.name){
			return true;
		}else{
			this._router.navigate(['/']);
			return false;
		}
	}
}
