import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from 'src/app/services/user.service';
import { TaskService } from 'src/app/services/task.service';
import { Task } from 'src/app/models/task';

@Component({
  selector: 'app-task-create',
  templateUrl: './task-create.component.html',
  styleUrls: ['./task-create.component.css'],
  providers: [UserService, TaskService]
})
export class TaskCreateComponent implements OnInit {

  public status: string;
  public page_title: string;
  public identity: any;
  public token: any;
  public task: Task;
  public check: boolean;

  constructor(
    private _router: Router,
    private _userService: UserService,
    private _taskService: TaskService
  ) { 
    this.status = "";
    this.check = false;
    this.page_title = "New task";
    this.identity = _userService.getIdentity();
    this.token = _userService.getToken();
    this.task = new Task(1, this.identity.sub, "", "", "", "");
  }

  ngOnInit(): void {
  }

  onSubmit(form: any){
    this._taskService.create(this.token, this.task).subscribe(
      response => {
        if(response.status == 'success'){
          this.status = 'success';
          form.reset();
        }else{
          this.status = 'error';
        }
      },
      error => {
        this.status = 'error';
        console.log(error);
      }
    );
  }

  onCheckBoxChange(event: any){
    console.log("on checkBox");
  }
}
