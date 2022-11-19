DROP TABLE IF EXISTS comment;
DROP TABLE IF EXISTS appointment;
DROP TABLE IF EXISTS ticket;
DROP TABLE IF EXISTS user;

CREATE TABLE user (
    email varchar(255) NOT NULL,
    pwd varchar(24) NOT NULL,
    first_name varchar(255) NOT NULL,
    last_name varchar(255) NOT NULL,
    date_of_birth date NOT NULL,
    residence varchar(255) NOT NULL,
    access_type varchar(12) NOT NULL, /* USER | ADMIN | CITYMAN | TECHNICIAN */
    specialization varchar(255) NULL,
    added_by varchar(255) NULL,
    PRIMARY KEY (email),
    FOREIGN KEY (added_by)  REFERENCES user(email)
);

CREATE TABLE ticket (
    id_ticket int NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL,
    category varchar(20) NOT NULL, /* DIRTY STREETS | ROADS | PLAYGROUNDS | BENCHES | ABANDONED VEHICLES | ILLEGAL DUMPS | VEGETATION | VANDALISM | OTHERS */
    descript varchar(1024) NOT NULL,
    cond varchar(12) NOT NULL, /* UNDER REVIEW | IN PROGRESS | DONE | SUSPENDED | REJECTED */
    author varchar(255) NOT NULL,
    date_add datetime NOT NULL,
    /* TODO - PICTURES MISSING */
    PRIMARY KEY(id_ticket),
    FOREIGN KEY(author) REFERENCES user(email)
);

CREATE TABLE appointment (
    id_appointment int NOT NULL AUTO_INCREMENT,
    author varchar(255) NOT NULL,
    assignee varchar(255) NOT NULL,
    descript varchar(1024) NOT NULL,
    estimation_date date NULL,
    cond varchar(12) NOT NULL,  /* IN-PROGRESS | DONE | SUSPENDED */
    time_spent int NOT NULL, /* in hours */
    parent_ticket int NOT NULL,
    PRIMARY KEY(id_appointment),
    FOREIGN KEY(author) REFERENCES user(email),
    FOREIGN KEY(assignee) REFERENCES user(email),
    FOREIGN KEY(parent_ticket) REFERENCES ticket(id_ticket)
);

CREATE TABLE comment (
    id_comment int NOT NULL AUTO_INCREMENT,
    content varchar(1024) NOT NULL,
    author varchar(255) NOT NULL,
    parent_ticket int NOT NULL,
    date_add datetime NOT NULL,
    PRIMARY KEY(id_comment),
    FOREIGN KEY(author) REFERENCES user(email),
    FOREIGN KEY(parent_ticket) REFERENCES ticket(id_ticket)
);

-- INSERT USERS --

INSERT INTO user(first_name, last_name, date_of_birth, residence, access_type, specialization, added_by, email, pwd)
VALUES ('Fero', 'Mrkva', date('1979-08-11'), 'Main cemetery', 'ADMIN', NULL, NULL, 'admin', 'admin');

INSERT INTO user(first_name, last_name, date_of_birth, residence, access_type, specialization, added_by, email, pwd)
VALUES ('Igor', 'Celer', date('1969-04-20'), 'Old building next to main cemetery', 'CITYMAN', NULL, 'admin', 'manager', 'manager');

INSERT INTO user(first_name, last_name, date_of_birth, residence, access_type, specialization, added_by, email, pwd)
VALUES ('Jozef', 'Mak', date('1989-11-17'), 'Parking lot near main cemetery', 'TECHNICIAN', 'Mandatory community service', 'manager', 'technician01', 'technician01');

INSERT INTO user(first_name, last_name, date_of_birth, residence, access_type, specialization, added_by, email, pwd)
VALUES ('Peter', 'Slivka', date('1989-11-17'), 'Tent in the park', 'TECHNICIAN', 'Gardener', 'manager', 'technician02', 'technician02');

INSERT INTO user(first_name, last_name, date_of_birth, residence, access_type, specialization, added_by, email, pwd)
VALUES ('Martin', 'Kapusta', date('1998-10-29'), 'PPV A01', 'USER', NULL, NULL, 'mkapusta@gmail.com', 'kapusta1');

INSERT INTO user(first_name, last_name, date_of_birth, residence, access_type, specialization, added_by, email, pwd)
VALUES ('Michal', 'Zemiak', date('2001-01-24'), 'PPV A03', 'USER', NULL, NULL, 'mzemiak@gmail.com', 'zemiak1');

INSERT INTO user(first_name, last_name, date_of_birth, residence, access_type, specialization, added_by, email, pwd)
VALUES ('Peter', 'Slivka', date('1989-11-17'), 'Tent in the park', 'USER', NULL, NULL, 'pslivka@gmail.com', 'slivka1');

-- INSERT TICKETS --

INSERT INTO ticket(title, category, descript, cond, author, date_add)
VALUE ('Homeless man in park', 'VEGETATION', 'There is a homeless man in the park.', 'IN PROGRESS', 'mkapusta@gmail.com', '2021-11-29 22:51:32');

INSERT INTO ticket(title, category, descript, cond, author, date_add)
VALUE ('Abandoned car near cemetery', 'ABANDONED VEHICLES', 'Rusted mazda in the parking lot.', 'DONE', 'mkapusta@gmail.com', '2022-07-11 12:34:12');

INSERT INTO ticket(title, category, descript, cond, author, date_add)
VALUE ('Graffiti on walls of Institute for blind people', 'VANDALISM', 'Somebody painted images of glasses on the walls.', 'SUSPENDED', 'mkapusta@gmail.com', '2021-12-23 23:59:10');

INSERT INTO ticket(title, category, descript, cond, author, date_add)
VALUE ('Old couch in the parking lot', 'ILLEGAL DUMPS', 'Somebody threw out old couch.', 'IN PROGRESS', 'mzemiak@gmail.com', '2021-04-20 12:21:47');

INSERT INTO ticket(title, category, descript, cond, author, date_add)
VALUE ('Unnecessary irrigation in parks', 'VEGETATION', 'Turn off the irrigation, it is flooding my home.', 'DONE', 'pslivka@gmail.com', '2011-07-13 07:41:37');

INSERT INTO ticket(title, category, descript, cond, author, date_add)
VALUE ('Noisy people near PPVs bar', 'OTHERS', 'Stop selling alcohol.', 'SUSPENDED', 'mzemiak@gmail.com', '2022-12-24 22:12:57');

INSERT INTO ticket(title, category, descript, cond, author, date_add)
VALUE ('Stray dog pees on people and things', 'VEGETATION', 'Call animal control on stray dog in the park.', 'IN PROGRESS', 'mzemiak@gmail.com', '1998-01-03 11:31:07');

INSERT INTO ticket(title, category, descript, cond, author, date_add)
VALUE ('Old couch in the park', 'ILLEGAL DUMPS', 'There is an old smelly couch in the park.', 'UNDER REVIEW', 'mzemiak@gmail.com', '2021-04-20 14:41:47');

-- INSERT APPOINTMENTS --

INSERT INTO appointment(author, assignee, descript, estimation_date, cond, time_spent, parent_ticket)
VALUES ('manager', 'technician02', 'The man in a bush is stinky',  date('2024-08-11'), 'IN PROGRESS', 2, 1);

INSERT INTO appointment(author, assignee, descript, estimation_date, cond, time_spent, parent_ticket)
VALUES ('manager', 'technician01', 'Parks do not need to be irrigated',  date('2025-01-12'), 'DONE', 34, 5);

INSERT INTO appointment(author, assignee, descript, estimation_date, cond, time_spent, parent_ticket)
VALUES ('manager', 'technician01', 'Noise from pub near PPV',  date('2022-12-24'), 'SUSPENDED', 13, 6);

INSERT INTO appointment(author, assignee, descript, estimation_date, cond, time_spent, parent_ticket)
VALUES ('manager', 'technician02', 'Dog pees on my bike',  date('2025-01-03'), 'IN PROGRESS', 20, 7);

INSERT INTO appointment(author, assignee, descript, estimation_date, cond, time_spent, parent_ticket)
VALUES ('manager', 'technician02', 'Rusted mazda in the parking lot.',  date('2023-01-09'), 'DONE', 11, 2);

INSERT INTO appointment(author, assignee, descript, estimation_date, cond, time_spent, parent_ticket)
VALUES ('manager', 'technician02', 'Somebody painted images of glasses on the walls.',  NULL, 'SUSPENDED', 0, 3);

INSERT INTO appointment(author, assignee, descript, estimation_date, cond, time_spent, parent_ticket)
VALUES ('manager', 'technician01', 'Somebody threw out old couch.',  date('2022-12-20'), 'IN-PROGRESS', 6, 4);

-- INSERT COMMENTS --

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUES ('He peed on my house too, luckily the irrigation cleaned it.', 'pslivka@gmail.com', 7, '2018-07-11 10:27:42');

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUES ('Dont bother, i already took it to my tent.', 'pslivka@gmail.com', 6, '2021-04-20 13:31:47');

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUES ('It wasnt rusty last week, but it was raining for few days.', 'mzemiak@gmail.com', 2, '2022-07-12 10:14:44');

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUES ('He should have stored it in the garage.', 'pslivka@gmail.com', 2, '2022-07-12 10:18:44');

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUES ('Why bother, they cant see it anyway.', 'admin', 3, '2021-12-24 10:24:59');

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUE ('After this post, the rusted car is missing wheels.', 'mzemiak@gmail.com', 2, '2022-08-11 10:32:54');

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUE ('In my younger years children were good and not nasty like today.', 'mzemiak@gmail.com', 3, '2022-08-11 11:12:09');

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUE ('If butter was not so expensive, we could eat real bread and not just drink it.', 'mkapusta@gmail.com', 6, '2023-02-02 4:20:54');

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUE ('I sadhuwh hink it are okey, we waant funnnn', 'pslivka@gmail.com', 6, '2023-01-02 19:22:47');

INSERT INTO comment(content, author, parent_ticket, date_add)
VALUE ('But my dog goes there to drink water when he is thirsty.', 'mkapusta@gmail.com', 5, '2022-11-28 16:02:38');