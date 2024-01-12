DROP DATABASE IF EXISTS scheduler;
CREATE DATABASE scheduler;
USE scheduler;

CREATE TABLE Classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255) DEFAULT '\'\'' NOT NULL,
    credits INT DEFAULT 4 NOT NULL,
    fall ENUM('Yes', 'No') DEFAULT 'No' NOT NULL,
    winter ENUM('Yes', 'No') DEFAULT 'No' NOT NULL,
    spring ENUM('Yes', 'No') DEFAULT 'No' NOT NULL,
    summer ENUM('Yes', 'No') DEFAULT 'No' NOT NULL,
    active ENUM('Yes', 'No') DEFAULT 'Yes' NOT NULL,
    CONSTRAINT uq_Class_name_credit UNIQUE (name , credits)
)  ENGINE=INNODB;

CREATE TABLE Majors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(256) DEFAULT '' NOT NULL,
    active ENUM('Yes', 'No') DEFAULT 'Yes' NOT NULL,
    CONSTRAINT uq_Major_name UNIQUE (name)
)  ENGINE=INNODB;

CREATE TABLE Prerequisites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    prerequisite_id INT NOT NULL,
    minimum_grade INT DEFAULT 20 NOT NULL,
    CONSTRAINT uq_Prerequisite_class_prerequisite UNIQUE (class_id , prerequisite_id),
    CONSTRAINT fk_Prerequisite_class FOREIGN KEY (class_id)
        REFERENCES Classes (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Prerequisite_prerequisite FOREIGN KEY (prerequisite_id)
        REFERENCES Classes (id)
        ON UPDATE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    major_id INT DEFAULT 0 NOT NULL,
    year INT NOT NULL,
    credits INT DEFAULT 0 NOT NULL,
    elective_credits INT DEFAULT 0 NOT NULL,
    active ENUM('Yes', 'No') DEFAULT 'Yes' NOT NULL,
    CONSTRAINT uq_Program_major_year UNIQUE (major_id , year),
    CONSTRAINT fk_Program_major FOREIGN KEY (major_id)
        REFERENCES Majors (id)
        ON UPDATE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    sequence INT NULL,
    name VARCHAR(256) NULL,
    CONSTRAINT uq_Checklist_program_sequence UNIQUE (program_id , sequence),
    CONSTRAINT fk_Checklist_program FOREIGN KEY (program_id)
        REFERENCES Programs (id)
        ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Program_Classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    class_id INT NOT NULL,
    minimum_grade INT DEFAULT 20 NOT NULL,
    sequence_no INT DEFAULT 1 NOT NULL,
    template_qtr INT NULL,
    template_year INT NULL,
    required ENUM('Yes', 'No') DEFAULT 'Yes' NOT NULL,
    CONSTRAINT fk_Program_Class_class FOREIGN KEY (class_id)
        REFERENCES Classes (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Program_Class_program FOREIGN KEY (program_id)
        REFERENCES Programs (id)
        ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Replacement_Classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    required_id INT NOT NULL,
    replacement_id INT NOT NULL,
    note TEXT NULL,
    CONSTRAINT uq_Replacement_Class_program_required_replacement UNIQUE (program_id , required_id , replacement_id),
    CONSTRAINT fk_Replacement_program FOREIGN KEY (program_id)
        REFERENCES Programs (id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_Replacement_replacement FOREIGN KEY (replacement_id)
        REFERENCES Classes (id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_Replacement_required FOREIGN KEY (required_id)
        REFERENCES Classes (id)
        ON UPDATE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first VARCHAR(256) DEFAULT '\'\'' NOT NULL,
    last VARCHAR(256) DEFAULT '\'\'' NOT NULL,
    cwu_id INT NOT NULL,
    email VARCHAR(256) NOT NULL,
    phone VARCHAR(32) NULL,
    address VARCHAR(256) NULL,
    postbaccalaureate ENUM('Yes', 'No') NULL,
    withdrawing ENUM('Yes', 'No') NULL,
    veterans_benefits ENUM('Yes', 'No') NULL,
    active ENUM('Yes', 'No') DEFAULT 'Yes' NOT NULL,
    non_stem_majors VARCHAR(256) NULL,
    CONSTRAINT uq_Student_cwuid UNIQUE (cwu_id),
    CONSTRAINT uq_Student_email UNIQUE (email)
)  ENGINE=INNODB;

CREATE TABLE Student_Checklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    checklist_id INT NOT NULL,
    CONSTRAINT uq_Student_Checklist_student_checklist UNIQUE (student_id , checklist_id),
    CONSTRAINT fk_Student_Checklist_checklist FOREIGN KEY (checklist_id)
        REFERENCES Checklists (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Student_Checklist_student FOREIGN KEY (student_id)
        REFERENCES Students (id)
        ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Student_Classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    term INT NOT NULL,
    CONSTRAINT fk_Student_Class_class FOREIGN KEY (class_id)
        REFERENCES Classes (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Student_Class_student FOREIGN KEY (student_id)
        REFERENCES Students (id)
        ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Electives (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_class_id INT NOT NULL,
    program_id INT NOT NULL,
    CONSTRAINT fk_Elective_program FOREIGN KEY (program_id)
        REFERENCES Programs (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Elective_student_classes FOREIGN KEY (student_class_id)
        REFERENCES Student_Classes (id)
        ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    name VARCHAR(255) DEFAULT '' NOT NULL,
    CONSTRAINT uq_Template_program_name UNIQUE (program_id , name),
    CONSTRAINT fk_Template_program FOREIGN KEY (program_id)
        REFERENCES Programs (id)
        ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Template_Classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    class_id INT NOT NULL,
    quarter INT NOT NULL,
    year INT NOT NULL,
    CONSTRAINT fk_Template_Class_class FOREIGN KEY (class_id)
        REFERENCES Classes (id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_Template_Class_template FOREIGN KEY (template_id)
        REFERENCES Templates (id)
        ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(255) DEFAULT '' NOT NULL,
    password VARCHAR(255) DEFAULT '' NOT NULL,
    name VARCHAR(255) DEFAULT '' NOT NULL,
    program_id INT NULL,
    superuser ENUM('Yes', 'No') DEFAULT 'No' NOT NULL,
    last VARCHAR(255) NULL,
    first VARCHAR(255) NULL,
    CONSTRAINT uq_User_login UNIQUE (login),
    CONSTRAINT fk_User_program FOREIGN KEY (program_id)
        REFERENCES Programs (id)
        ON UPDATE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Journal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
    note TEXT NOT NULL,
    student_id INT NULL,
    class_id INT NULL,
    program_id INT NULL,
    major_id INT NULL,
    CONSTRAINT fk_Journal_class FOREIGN KEY (class_id)
        REFERENCES Classes (id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_Journal_major FOREIGN KEY (major_id)
        REFERENCES Majors (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Journal_program FOREIGN KEY (program_id)
        REFERENCES Programs (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Journal_student FOREIGN KEY (student_id)
        REFERENCES Students (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Journal_user FOREIGN KEY (user_id)
        REFERENCES Users (id)
        ON UPDATE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    datetime DATETIME NOT NULL,
    flagged ENUM('Yes', 'No') DEFAULT 'No' NOT NULL,
    note TEXT NOT NULL,
    user_id INT NOT NULL,
    CONSTRAINT fk_Note_student FOREIGN KEY (student_id)
        REFERENCES Students (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Note_user FOREIGN KEY (user_id)
        REFERENCES Users (id)
        ON UPDATE CASCADE
)  ENGINE=INNODB;

CREATE TABLE Student_Programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    program_id INT NOT NULL,
    user_id INT NOT NULL,
    CONSTRAINT uq_student_program_user UNIQUE (student_id , program_id , user_id),
    CONSTRAINT fk_Student_Program_program FOREIGN KEY (program_id)
        REFERENCES Programs (id)
        ON UPDATE CASCADE,
    CONSTRAINT fk_Student_Program_student FOREIGN KEY (student_id)
        REFERENCES Students (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_Student_Program_user FOREIGN KEY (user_id)
        REFERENCES Users (id)
        ON UPDATE CASCADE
)  ENGINE=INNODB;

CREATE TABLE User_Programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    program_id INT NOT NULL,
    can_edit ENUM('YES', 'NO') DEFAULT 'NO' NOT NULL,
    sequence INT NULL,
    CONSTRAINT uq_User_Program_user_program UNIQUE (user_id , program_id),
    CONSTRAINT fk_User_Program_program FOREIGN KEY (program_id)
        REFERENCES Programs (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_User_Program_user FOREIGN KEY (user_id)
        REFERENCES Users (id)
        ON UPDATE CASCADE ON DELETE CASCADE
)  ENGINE=INNODB;


