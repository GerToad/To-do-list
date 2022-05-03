import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { UserService } from 'src/app/services/user.service';
import { TaskService } from 'src/app/services/task.service';
import { Task } from 'src/app/models/task';

@Component({
  selector: 'app-task-edit',
  templateUrl: './task-edit.component.html',
  styleUrls: ['./task-edit.component.css'],
  providers: [UserService, TaskService]
})
export class TaskEditComponent implements OnInit {

  public status: string;
  public page_title: string;
  public identity: any;
  public token: any;
  public task: Task;
  public check: boolean;

  constructor(
    private _route: ActivatedRoute,
    private _userService: UserService,
    private _taskService: TaskService
  ) { 
    this.status = "";
    this.check = true;
    this.page_title = "Edit task";
    this.identity = _userService.getIdentity();
    this.token = _userService.getToken();
    this.task = new Task(1, this.identity.sub, "", "", "", "");
  }

  ngOnInit(): void {
    this.getTask();
  }

  getTask(){
    this._route.params.subscribe(params => {
      var id = +params['id'];

      this._taskService.task(this.token, id).subscribe(
        response => {
          if(response.status == 'success'){
            this.task = response.task;
          }
        },
        error =>{
          console.log(error);
          this.status = 'error';
        }
      );
    });
  }

  onSubmit(form: any){
    this._taskService.update(this.token, this.task).subscribe(
      response => {
        if(response.status == 'success'){
          this.status = 'success';
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
    if(event.target.checked){
      this.task.status = "Complete";
    }else{
      this.task.status = "Incomplete";
    }
    console.log(this.task.status);
    this._taskService.check(this.token, this.task).subscribe(
      response => {
        if(response.task.status == 'complete'){
          this.status = 'checked';
          //console.log(response.task);
        }else{
          this.status = 'unchecked';
        }
      },
      error => {
        this.status = 'error';
        console.log(error);
      }
    );
  }
}
