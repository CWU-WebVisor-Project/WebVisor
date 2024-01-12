create table Classes
(
    id      int auto_increment
        primary key,
    name    varchar(255)                      not null,
    title   varchar(255)       default '''''' not null,
    credits int                default 4      not null,
    fall    enum ('Yes', 'No') default 'No'   not null,
    winter  enum ('Yes', 'No') default 'No'   not null,
    spring  enum ('Yes', 'No') default 'No'   not null,
    summer  enum ('Yes', 'No') default 'No'   not null,
    active  enum ('Yes', 'No') default 'Yes'  not null,
    constraint uq_Class_name_credit
        unique (name, credits)
)
    engine = InnoDB;

create table Majors
(
    id     int auto_increment
        primary key,
    name   varchar(256)       default ''    not null,
    active enum ('Yes', 'No') default 'Yes' not null,
    constraint uq_Major_name
        unique (name)
)
    engine = InnoDB;

create table Prerequisites
(
    id              int auto_increment
        primary key,
    class_id        int            not null,
    prerequisite_id int            not null,
    minimum_grade   int default 20 not null,
    constraint uq_Prerequisite_class_prerequisite
        unique (class_id, prerequisite_id),
    constraint fk_Prerequisite_class
        foreign key (class_id) references Classes (id)
            on update cascade on delete cascade,
    constraint fk_Prerequisite_prerequisite
        foreign key (prerequisite_id) references Classes (id)
            on update cascade
)
    engine = InnoDB;

create table Programs
(
    id               int auto_increment
        primary key,
    major_id         int                default 0     not null,
    year             int                              not null,
    credits          int                default 0     not null,
    elective_credits int                default 0     not null,
    active           enum ('Yes', 'No') default 'Yes' not null,
    constraint uq_Program_major_year
        unique (major_id, year),
    constraint fk_Program_major
        foreign key (major_id) references Majors (id)
            on update cascade
)
    engine = InnoDB;

create table Checklists
(
    id         int auto_increment
        primary key,
    program_id int          not null,
    sequence   int          null,
    name       varchar(256) null,
    constraint uq_Checklist_program_sequence
        unique (program_id, sequence),
    constraint fk_Checklist_program
        foreign key (program_id) references Programs (id)
            on update cascade on delete cascade
)
    engine = InnoDB;

create table Program_Classes
(
    id            int auto_increment
        primary key,
    program_id    int                              not null,
    class_id      int                              not null,
    minimum_grade int                default 20    not null,
    sequence_no   int                default 1     not null,
    template_qtr  int                              null,
    template_year int                              null,
    required      enum ('Yes', 'No') default 'Yes' not null,
    constraint fk_Program_Class_class
        foreign key (class_id) references Classes (id)
            on update cascade on delete cascade,
    constraint fk_Program_Class_program
        foreign key (program_id) references Programs (id)
            on update cascade on delete cascade
)
    engine = InnoDB;

create table Replacement_Classes
(
    id             int auto_increment
        primary key,
    program_id     int  not null,
    required_id    int  not null,
    replacement_id int  not null,
    note           text null,
    constraint uq_Replacement_Class_program_required_replacement
        unique (program_id, required_id, replacement_id),
    constraint fk_Replacement_program
        foreign key (program_id) references Programs (id)
            on update cascade,
    constraint fk_Replacement_replacement
        foreign key (replacement_id) references Classes (id)
            on update cascade,
    constraint fk_Replacement_required
        foreign key (required_id) references Classes (id)
            on update cascade
)
    engine = InnoDB;

create table Students
(
    id                int auto_increment
        primary key,
    first             varchar(256)       default '''''' not null,
    last              varchar(256)       default '''''' not null,
    cwu_id            int                               not null,
    email             varchar(256)                      not null,
    phone             varchar(32)                       null,
    address           varchar(256)                      null,
    postbaccalaureate enum ('Yes', 'No')                null,
    withdrawing       enum ('Yes', 'No')                null,
    veterans_benefits enum ('Yes', 'No')                null,
    active            enum ('Yes', 'No') default 'Yes'  not null,
    non_stem_majors   varchar(256)                      null,
    constraint uq_Student_cwuid
        unique (cwu_id),
    constraint uq_Student_email
        unique (email)
)
    engine = InnoDB;

create table Student_Checklists
(
    id           int auto_increment
        primary key,
    student_id   int not null,
    checklist_id int not null,
    constraint uq_Student_Checklist_student_checklist
        unique (student_id, checklist_id),
    constraint fk_Student_Checklist_checklist
        foreign key (checklist_id) references Checklists (id)
            on update cascade on delete cascade,
    constraint fk_Student_Checklist_student
        foreign key (student_id) references Students (id)
            on update cascade on delete cascade
)
    engine = InnoDB;

create table Student_Classes
(
    id         int auto_increment
        primary key,
    student_id int not null,
    class_id   int not null,
    term       int not null,
    constraint fk_Student_Class_class
        foreign key (class_id) references Classes (id)
            on update cascade on delete cascade,
    constraint fk_Student_Class_student
        foreign key (student_id) references Students (id)
            on update cascade on delete cascade
)
    engine = InnoDB;

create table Electives
(
    id               int auto_increment
        primary key,
    student_class_id int not null,
    program_id       int not null,
    constraint fk_Elective_program
        foreign key (program_id) references Programs (id)
            on update cascade on delete cascade,
    constraint fk_Elective_student_classes
        foreign key (student_class_id) references Student_Classes (id)
            on update cascade on delete cascade
)
    engine = InnoDB;

create table Templates
(
    id         int auto_increment
        primary key,
    program_id int                     not null,
    name       varchar(255) default '' not null,
    constraint uq_Template_program_name
        unique (program_id, name),
    constraint fk_Template_program
        foreign key (program_id) references Programs (id)
            on update cascade on delete cascade
)
    engine = InnoDB;

create table Template_Classes
(
    id          int auto_increment
        primary key,
    template_id int not null,
    class_id    int not null,
    quarter     int not null,
    year        int not null,
    constraint fk_Template_Class_class
        foreign key (class_id) references Classes (id)
            on update cascade,
    constraint fk_Template_Class_template
        foreign key (template_id) references Templates (id)
            on update cascade on delete cascade
)
    engine = InnoDB;

create table Users
(
    id         int auto_increment
        primary key,
    login      varchar(255)       default ''   not null,
    password   varchar(255)       default ''   not null,
    name       varchar(255)       default ''   not null,
    program_id int                             null,
    superuser  enum ('Yes', 'No') default 'No' not null,
    last       varchar(255)                    null,
    first      varchar(255)                    null,
    constraint uq_User_login
        unique (login),
    constraint fk_User_program
        foreign key (program_id) references Programs (id)
            on update cascade
)
    engine = InnoDB;

create table Journal
(
    id         int auto_increment
        primary key,
    user_id    int                                 not null,
    date       timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    note       text                                not null,
    student_id int                                 null,
    class_id   int                                 null,
    program_id int                                 null,
    major_id   int                                 null,
    constraint fk_Journal_class
        foreign key (class_id) references Classes (id)
            on update cascade,
    constraint fk_Journal_major
        foreign key (major_id) references Majors (id)
            on update cascade on delete cascade,
    constraint fk_Journal_program
        foreign key (program_id) references Programs (id)
            on update cascade on delete cascade,
    constraint fk_Journal_student
        foreign key (student_id) references Students (id)
            on update cascade on delete cascade,
    constraint fk_Journal_user
        foreign key (user_id) references Users (id)
            on update cascade
)
    engine = InnoDB;

create table Notes
(
    id         int auto_increment
        primary key,
    student_id int                             not null,
    datetime   datetime                        not null,
    flagged    enum ('Yes', 'No') default 'No' not null,
    note       text                            not null,
    user_id    int                             not null,
    constraint fk_Note_student
        foreign key (student_id) references Students (id)
            on update cascade on delete cascade,
    constraint fk_Note_user
        foreign key (user_id) references Users (id)
            on update cascade
)
    engine = InnoDB;

create table Student_Programs
(
    id         int auto_increment
        primary key,
    student_id int not null,
    program_id int not null,
    user_id    int not null,
    constraint uq_student_program_user
        unique (student_id, program_id, user_id),
    constraint fk_Student_Program_program
        foreign key (program_id) references Programs (id)
            on update cascade,
    constraint fk_Student_Program_student
        foreign key (student_id) references Students (id)
            on update cascade on delete cascade,
    constraint fk_Student_Program_user
        foreign key (user_id) references Users (id)
            on update cascade
)
    engine = InnoDB;

create table User_Programs
(
    id         int auto_increment
        primary key,
    user_id    int                             not null,
    program_id int                             not null,
    can_edit   enum ('YES', 'NO') default 'NO' not null,
    sequence   int                             null,
    constraint uq_User_Program_user_program
        unique (user_id, program_id),
    constraint fk_User_Program_program
        foreign key (program_id) references Programs (id)
            on update cascade on delete cascade,
    constraint fk_User_Program_user
        foreign key (user_id) references Users (id)
            on update cascade on delete cascade
)
    engine = InnoDB;


