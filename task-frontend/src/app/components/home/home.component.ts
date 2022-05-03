import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from 'src/app/services/user.service';
import { TaskService } from 'src/app/services/task.service';
import { Task } from 'src/app/models/task';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css'],
  providers: [UserService, TaskService]
})
export class HomeComponent implements OnInit {

  public page_title: string;
  public identity: any;
  public token: any;
  public tasks: any;
  public task: Task;
  public page: any;
  public next_page: any;
  public prev_page: any;
  public number_pages: any;

  constructor(
    private _userService: UserService,
    private _taskService: TaskService,
    private _route: ActivatedRoute,
    private _router: Router
  ) { 
    this.page_title = "All tasks"
    this.identity = _userService.getIdentity();
    this.token = _userService.getToken();
    this.task = new Task(1, 1, "", "", "", "");
  }

  ngOnInit(): void {
    this.actualPage();
  }

  actualPage(){
    this._route.params.subscribe(params => {
      var page = +params['page'];
      if(!page){
        page = 1;
        this.prev_page = 1;
        this.next_page = 2;
      }
      this.getTasks(page);
    });
  }

  getTasks(page: any){
    this._taskService.tasks(this.token, page).subscribe(
      response => {
        this.tasks = response.tasks;

        var number_pages = [];
        for(var i=1; i<=response.total_pages; i++){
          number_pages.push(i);
        }
        this.number_pages = number_pages;

        if(page >= 2){
          this.prev_page = page-1;
        }else{
          this.prev_page = 1;
        }

        if(page < response.total_pages){
          this.next_page = page+1;
        }else{
          this.next_page = response.total_pages;
        }
      },
      error => {
        console.log(error);
      }
    );
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
