import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { User } from 'src/app/models/user';
import { UserService } from 'src/app/services/user.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css'],
  providers: [UserService]
})

export class LoginComponent implements OnInit {
  public page_title: string;
  public user: User;
  public status: string;
  public token: any;
  public identity: any;

  constructor(
    private _userService: UserService,
    private _router: Router,
    private _route: ActivatedRoute
  ) { 
    this.page_title = "Login";
    this.status = "";
    this.user = new User(1, '', '', '', '', '');
  }

  ngOnInit(): void {
    this.logout();
  }

  onSubmit(form: any){
    this._userService.login(this.user).subscribe(
      response => {
        if(!response.status || response.status != 'error'){
          this.status = 'success';
          this.identity = response;

          this._userService.login(this.user, true).subscribe(
            response => {
              if(!response.status || response.status != 'error'){
                this.token = response;

                console.log(this.identity);
                console.log(this.token);

                this._router.navigate(['/home']);

                localStorage.setItem('token', this.token);
                localStorage.setItem('identity', JSON.stringify(this.identity));
              }
            },error => {
              console.log(error);
            }
          );
        }else{
          this.status = 'error';
        }      
      },
      error => {
        console.log(error);
      }
    );
  }

  logout(){
    this._route.params.subscribe(params => {
      let sure = +params['sure'];

      if(sure == 1){
        localStorage.removeItem('identity');
        localStorage.removeItem('token');

        this.identity = null;
        this.token = null;

        this._router.navigate(['/home']);
      }
    })
  }

}
