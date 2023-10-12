CREATE DATABASE php_login_management;
CREATE DATABASE php_login_management_test;
use php_login_management;
use php_login_management_test;

CREATE TABLE users(
    id varchar(255) primary key ,
    name varchar(255) not null ,
    password varchar(255) not null
)engine InnoDB;

create table sessions(
    id varchar(255) primary key ,
    user_id varchar(255)not null
)engine InnoDB;

 alter table sessions
add CONSTRAINT fk_sessions_user
    FOREIGN KEY (user_id)
        REFERENCES users(id);
