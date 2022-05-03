import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { Task } from 'src/app/models/task';
import { TaskService } from 'src/app/services/task.service';
import { UserService } from 'src/app/services/user.service';

@Component({
  selector: 'app-search',
  templateUrl: '../home/home.component.html',
  styleUrls: ['./search.component.css'],
  providers: [TaskService, UserService]
})
export class SearchComponent implements OnInit {

  public tasks: Array<Task>;
  public task: Task;
  public identity: any;
  public token: any;
  public page_title: string;
  public next_page: any;
  public prev_page: any;
  public number_pages: any;
  public no_paginate: boolean;

  constructor(
    private _route: ActivatedRoute,
    private _router: Router,
    private _taskService: TaskService,
    private _userService: UserService
  ) {
    this.tasks = Array();
    this.identity = _userService.getIdentity();
    this.token = _userService.getToken();
    this.page_title = 'Search: ';
    this.no_paginate = true;
    this.task = new Task(1, this.identity.sub, "", "", "", "");
  }

  ngOnInit(): void {
    this._route.params.subscribe(params => {
      var search = params['task'];
      console.log("this is " + search);

      this.page_title = this.page_title + search;
      this.getTasks(search);
    });
  }

  getTasks(search: string){
    this._taskService.search(this.token, search).subscribe(
      response => {
        if(response.tasks){
          this.tasks = response.tasks;
        }
      },
      error => {
        console.log(error);
      }
    )
  }

  onCheckBoxChange(event: any, id: any){
    if(event.target.checked){
      this.task.status = "Complete";
    }else{
      this.task.status = "Incomplete";
    }
    console.log(this.task.status);
    this.task.id = id;
    this._taskService.check(this.token, this.task).subscribe(
      response => {
        if(response.task.status == 'complete'){
          //this.status = 'checked';
          console.log(response.task);
        }else{
          //this.status = 'unchecked';
        }
      },
      error => {
        //this.status = 'error';
        console.log(error);
      }
    );
  }
}
