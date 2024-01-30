# CWU WebVisor Project

## Overview

WebVisor is a web application designed for staff and faculty at Central Washington University (CWU) to create and manage academic plans for students. This application provides a centralized platform for advisors, instructors, and department staff to manage enrollments, majors, programs, and student information efficiently.

## Features

- **Student Information:** Add students to programs and modify academic plans. Keep track of class schedules and student progress.
- **Class Information:** Manage class details including credits and scheduling.
- **Program Information:** Administer various academic programs, including required and elective classes.
- **Major Information:** Oversee active and inactive majors and track student enrollment in each major.
- **Enrollments:** Monitor and display student enrollments in courses for each academic quarter.

## Prerequisites / Deployment

**Note:** The production environment is currently under construction; however, development environments are fully operational.

### Requirements

Before beginning the installation process, ensure you have the following:
- Xampp or Wamp Server
- MySQL Server
- Git

### Installation

1. **Install Xampp or Wamp Server:**
   - Xampp is recommended as it includes a MySQL Server and is available on most operating systems.
   - Download and install Xampp from [Apache Friends](https://www.apachefriends.org/).
   - If using Wamp, a separate SQL server installation is necessary. MySQL Dev Server can be downloaded from [MySQL Downloads](https://dev.mysql.com/downloads/mysql/).

### Setup

1. **Initialize the MySQL Database:**
   - Start the MySQL server.
   - Open PHPMyAdmin (e.g., `http://localhost/phpmyadmin`) and navigate to `http://localhost/phpmyadmin/index.php?route=/server/sql`.
   - Select 'Import' and import the `scheduler.sql` file from the repository.

2. **Start the PHP Apache Server:**
   - Launch the Apache Server using Xampp.
   - Navigate to the `htdocs` folder (e.g., `C:\xampp\htdocs\`).
   - Clone or copy the repository into this location:
     ```
     cd C:/xampp/htdocs/
     git clone https://github.com/CWU-WebVisor-Project/WebVisor.git
     ```

### Running the Application

Access the application at `http://localhost/Webvisor`.

## Contributing

We welcome contributions to the WebVisor Project. If you have suggestions or improvements, please fork the repository and create a pull request. Your input is greatly valued in enhancing the functionality and user experience of WebVisor.
