import { Component, OnInit } from '@angular/core';
import { User } from 'src/app/models/user';
import { UserService } from 'src/app/services/user.service';
import { global } from 'src/app/services/global';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css'],
  providers: [UserService]
})
export class RegisterComponent implements OnInit {

  public page_title: string;
  public user: User;
  public status: string;
  public token: string;

  constructor(
    private _userService: UserService
  ) {
    this.page_title = "Register";
    this.user = new User(1, '', '', '', '', '');
    this.status = "";
    this.token = "";
  }

  ngOnInit(): void {
    //console.log(this.user);
    //console.log(this._userService.test());
  }

  onSubmit(form: any){
    //console.log(this.user);
    this._userService.register(this.user).subscribe(
      response => {
        if(response.user && response.user.id){
          this.status = 'success';
          console.log(response.user);
          form.reset();
        }else{
          this.status = 'error';
        }
      },
      error => {
        console.log(error);
      }
    );
  }

}
