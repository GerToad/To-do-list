import { ModuleWithProviders } from "@angular/core";
import { Routes, RouterModule } from "@angular/router";

import { HomeComponent } from "./components/home/home.component";
import { LoginComponent } from "./components/login/login.component";
import { RegisterComponent } from "./components/register/register.component";
import { UserComponent } from "./components/user/user.component";
import { TaskCreateComponent } from "./components/task-create/task-create.component";
import { TaskEditComponent } from "./components/task-edit/task-edit.component";
import { SearchComponent } from "./components/search/search.component";

import { IdentityGuard } from "./services/identity.guard";

const appRoutes: Routes = [
	{ path: '', component: HomeComponent },
	{ path: 'home', component: HomeComponent },
	{ path: 'home/:page', component: HomeComponent },
	{ path: 'login', component: LoginComponent },
	{ path: 'logout/:sure', component: LoginComponent },
	{ path: 'register', component: RegisterComponent },
	{ path: 'settings', component: UserComponent, canActivate: [IdentityGuard] },
	{ path: 'task/create', component: TaskCreateComponent, canActivate: [IdentityGuard] },
	{ path: 'task/edit/:id', component: TaskEditComponent, canActivate: [IdentityGuard] },
	{ path: 'search/:task', component: SearchComponent },
	{ path: '**', component: HomeComponent },
];

export const appRoutingProviders: any[] = [];
export const routing: ModuleWithProviders<any> = RouterModule.forRoot(appRoutes);

