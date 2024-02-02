# ToDo App

ToDo app that could be used to maintain day-to-day tasks or list everything that we have to do and we can add more new tasks and delete the old ones once they are complete.

Primary Product requirements:

1. User should be able login and signup to the application.
2. Users can view a comprehensive list of all tasks in their to-do list.
3. Users can easily add new tasks to their to-do list, specifying relevant details like title, description, due date.
4. Users can edit existing tasks to update details such as title, description, due date as needed.
5. User should be able to remove the items from the todo list once he/she is done with the task.

 Secondary product requirements:

1. Users can set reminders or notifications for important tasks to ensure timely completion.
2. Users can attach files or links to tasks for additional reference or context.
3. Users can archive tasks for future reference or to maintain a record of completed tasks.

NF requirements:

1. Data consistency should be maintained while dealing with large number of users and there long todo lists.
2. Availability should also be maintained for the todo application.
3. The application should have  secure authentication to prevent unauthorized access to user accounts.
4. The application should be fault tolerant and secure the user data to prevent loss or corruption and provide a restoration process.
5. Scalability should also be considered when dealing with large amount of users and large volume of task items.



## Summary

* Create APIs for user registration and login to the web application. User credentials will be stored in mysql in the Users table.
* Once user is logged in, the home page will have the dashboard with user to-do list.
* Add more task button could be used to add more tasks into the to-do list.
* Delete task button will mark that task as complete and remove it from the list.
* Archive task button will mark it achieved and move it to the archive page.
* User can go to the archive page to see all the archived tasks.
* Tasks will have attributes like title, created by, due date, description, etc which can be updated by the user if it wants.
* Tasks will be stored in mysql database based on user_id.
* Since we have strong relationship between the user and the task we are using the relational database as it seems good fit for us.
* We’ll create a new table for the archive tasks based on the user_id and store them into that table this will help us to reduce the load on our unarchived task table.
* We can use NGINX as load balancer and reverse proxy for our server.




## API Contracts:

```
POST /login
req
{
   "username": String,
   "password": String
}
res: this api returns a Response type object with cookies set to sessionID

GET /login
- this api render the login.html.twig page
```

```
POST /register
req
{
    "username": String,
    "password": String   
}
res: this api redirects to login page

GET /register
- this api renders the register.html.twig page
```

```
GET /logout
- this api returns a Response type object with refreshed cookies
```

```
POST /addTask
req
{
    "title": String,
    "description": String,
    "IsArchived": Boolean,
    "dueDate": String,
 }
 - this api add new task into the Task entity 
 - after successful addition redirects to '/' route
```

```
GET /deleteTask/{taskId}

- this api deletes a particular task based on taskId only if it belongs to the logged in user
- after successful deletion redirects to '/' route
```





## Database

### User
<img src="https://github.com/abhnvv/todo/blob/main/User%20todo%20app%20Model%20.png" alt="User Table Model" width="250" height="200">

* User entity stores the user credentials such as username, password.
* We generate a new UUID and assign it to every new user.
* id is primary key
* We have creates index on username to find user by username quickly

### Task

<img src="https://github.com/abhnvv/todo/blob/main/task%20table%20model.png" alt="User Table Model" width="250" height="250">

