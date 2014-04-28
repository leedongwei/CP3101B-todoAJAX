DROP TABLE APPUSER CASCADE;
DROP TABLE USERTASK CASCADE;

CREATE TABLE APPUSER (
	USERNAME VARCHAR(32) PRIMARY KEY, 
	PASSWORD VARCHAR(64), 
	NAME VARCHAR(32), 
	EMAIL VARCHAR(32), 
	PIC VARCHAR(8)
);

CREATE TABLE USERTASK (
	TID NUMERIC(8) PRIMARY KEY, 
	USERNAME VARCHAR(32),
	TASKNAME VARCHAR(32), 
	TASKTIME VARCHAR(8), 
	TASKDONE VARCHAR(8)
);

INSERT INTO APPUSER VALUES (
	'hello',
	'f7b177278afa187ea2fbb4cf3c4fd4e963478ca286d9f46e835d9e3dee23075d',
	'John Smith',
	'john@nus.edu.sg',
	'1'
);

INSERT INTO APPUSER VALUES (
	'happycaterpie',
	'f7b177278afa187ea2fbb4cf3c4fd4e963478ca286d9f46e835d9e3dee23075d',
	'Caterpie',
	'caterpie@nus.edu.sg',
	'3'
);

INSERT INTO USERTASK VALUES (
	'0',
	'hello',
	'Fly to the Moon',
	'100',
	'50'
);

INSERT INTO USERTASK VALUES (
	'1',
	'hello',
	'Learn to sail',
	'10',
	'9'
);

INSERT INTO USERTASK VALUES (
	'2',
	'hello',
	'Climb the stairs',
	'5',
	'0'
);

INSERT INTO USERTASK VALUES (
	'3',
	'hello',
	'Watch grass grow',
	'8',
	'1'
);

INSERT INTO USERTASK VALUES (
	'4',
	'hello',
	'Eat breakfast',
	'4',
	'4'
);