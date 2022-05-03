import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { User } from 'src/app/models/user';
import { UserService } from 'src/app/services/user.service';
import { global } from 'src/app/services/global';

@Component({
  selector: 'app-user',
  templateUrl: './user.component.html',
  styleUrls: ['./user.component.css'],
  providers: [UserService]
})
export class UserComponent implements OnInit {

  public page_title: string;
  public user: User;
  public status: string;
  public afuConfig: any;
  public url: string;
  public token: any;
  public identity: any;

  constructor(
    private _userService: UserService,
    private _router: Router,
    private _route: ActivatedRoute
  ) {
    this.page_title = "Settings";
    this.status = "";
    this.url = global.url;
    this.token = this._userService.getToken();
    this.identity = this._userService.getIdentity();
    this.user = new User(
      this.identity.sub, 
      this.identity.name, 
      this.identity.surname,
      this.identity.email, 
      '','');
    this.afuConfig = {
      multiple: false,
      formatsAllowed: ".jpg, .jpeg, .png, .gif",
      maxSize: "50",
      uploadAPI: {
        url: this.url+"user/upload-avatar",
        headers: {
          "Authorization": this.token
        }
      },
      theme: "attachPin",
      hideProgressBar: false,
      hideResetBtn: true,
      hideSelectBtn: false,
      replaceTexts: {
        selectFileBtn: 'Select Files',
        resetBtn: 'Reset',
        uploadBtn: 'Upload',
        dragNDropBox: 'Drag N Drop',
        attachPinBtn: 'upload your avatar...',
        afterUploadMsg_success: 'Successfully Uploaded !',
        afterUploadMsg_error: 'Upload Failed !',
        sizeLimit: 'Size Limit'
      }
    };
  }

  ngOnInit(): void {
  }

  avatarUpload(data: any){
    let data_obj = data.body.user.image;
    console.log(data_obj);

    this.user.image = data_obj;
    console.log(this.user);
  }

  onSubmit(form: any){
    this._userService.update(this.user).subscribe(
      response => {
        if(!response.user){
          this.status = 'error';
        }else{
          this.status = 'success';
          localStorage.setItem('identity', JSON.stringify(this.user));
        }
      },
      error => {
        this.status = "error";
        console.log(error);
      }
    );
  }

}
