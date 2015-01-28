ALTER TABLE CLIENTS$CLIENTS ADD ACTIVITIES DOM_REFERENCE;
update system$templates_modules SET ACTIEF = 0 WHERE ID NOT IN (5,6);
DELETE FROM SUPPORT$ZIP_CODES Z WHERE CODE='' AND (SELECT COUNT(*) FROM SYSTEM$COLLECTORS C WHERE C.ZIP_CODE_ID = Z.ZIP_CODE_ID) = 0 AND (SELECT COUNT(*) FROM SYSTEM$USERS D WHERE D.ZIP_CODE_ID = Z.ZIP_CODE_ID) = 0 AND (SELECT COUNT(*) FROM FILES$DEBTORS E WHERE E.ZIP_CODE_ID = Z.ZIP_CODE_ID) = 0;
DROP TRIGGER files$bu_r_calc_values;
DROP TRIGGER FILES$P_SET_COMMISSION_BU;
DROP TRIGGER FILES$P_SET_COMMISSION_BI;
ALTER TABLE TEMP_PAYMENTS ADD INVOICE_REFERENCE DOM_REFERENCE;
ALTER TABLE CLIENTS$CLIENTS ADD ACTIVITIES DOM_REFERENCE;
ALTER TABLE CLIENTS$CLIENTS ADD ARTICLE DOM_REFERENCE;
ALTER TABLE CLIENTS$CLIENTS ADD COURT DOM_REFERENCE;

ALTER TABLE FILES$REFERENCES ADD INVOICE_DATE DATE;
DELETE FROM TEKSTEN WHERE CODE = 'LETTERS_SETTINGS';
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('LETTERS_SETTINGS','all','LETTERTYPE=Helvetica
LINE_HEIGHT=4
MARGIN_LEFT=20
MARGIN_TOP=20
MARGIN_RIGHT=20
SIZE=10
SIZESMALL=9
SIZEEXTRASMALL=7
ADDRESS_X=20
ADDRESS_Y=52
LOGOFILE = logo
LOGO_X=145
LOGO_Y=20
LOGO_H=50
IMAGEFILE= logo2
IMAGE_X=10
IMAGE_Y=15
IMAGE_H=60
FOOTER_Y=270
FOOTER_ALIGN=L
FOOTER_BORDER=T
SIGNFILE=sign
SIGN_HEIGHT=20
SIGN_ABOVE_TEXT=25','','','1');

DELETE FROM TEKSTEN WHERE CODE = 'IMPORT_COLUMS';
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('IMPORT_COLUMS','all','REFERENCE = 8
NAAM = 0
STRAAT = 1
POSTCODE =  2
GEMEENTE =  3
LAND = 4
GEBOORTEDATUM = X
TAAL = 6
TEL = 13
E_MAIL = 14
BTW =  5
BEDRAG = 10
INVOICE_REFERENCE = 9
INVOICE_BEDRAG =10
INVOICE_DATUM = 11
INVOICE_VERVALDATUM = 12
PARTNER = 15','','','1');

DELETE FROM TEKSTEN WHERE CODE = 'STARTUP_INCASSO';
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('STARTUP_INCASSO','all','0','','','1');

ALTER TABLE CLIENTS$CLIENTS ADD TRAIN_TYPE DOM_REFERENCE;
ALTER TABLE TRAIN ADD TRAIN_TYPE DOM_REFERENCE;
ALTER TABLE FILES$DEBTORS ADD PASS DOM_REFERENCE;
ALTER TABLE FILES$FILES ADD PARTNER VARCHAR(255);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('COMMISSION_CLIENT_ID','all','3658','','','1');
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('template_footer','all','','','','1');

ALTER TABLE TEMP_PAYMENTS ADD ACCOUNT_CODE DOM_CODE;

ALTER TABLE FILES$FILES ADD PARTNER VARCHAR(255);
ALTER TABLE FILES$DEBTORS ADD PASS VARCHAR(100);
ALTER TABLE FILES$REFERENCES ADD STATE_ID INTEGER;
create index ix_reference_state on FILES$REFERENCES computed by (STATE_ID);
UPDATE FILES$REFERENCES SET STATE_ID = '26' WHERE STATE_ID is null;
ALTER TABLE FILES$REFERENCES ADD DISPUTE SMALLINT;
create index ix_reference_dispute on FILES$REFERENCES computed by (DISPUTE);
ALTER TABLE FILES$REFERENCES ADD DISPUTE_DATE DATE;
create index ix_reference_dispute_date on FILES$REFERENCES computed by (DISPUTE_DATE);
ALTER TABLE FILES$REFERENCES ADD DISPUTE_DUEDATE DATE;
create index ix_reference_dispute_duedate on FILES$REFERENCES computed by (DISPUTE_DUEDATE);
ALTER TABLE FILES$REFERENCES ADD DISPUTE_ENDED_DATE DATE;
create index ix_reference_dispute_ended_date on FILES$REFERENCES computed by (DISPUTE_ENDED_DATE);
UPDATE FILES$REFERENCES SET DISPUTE=0;

create table REPORTS$DSO (
DSO_MONTH VARCHAR (2),
DSO_YEAR VARCHAR (4),
SALES DOM_MONEY,
DSO SMALLINT,
CREATION_USER DOM_CURRENT_USER,
CREATION_DATE DOM_DATE
);
create index IDX_REPORTS_DSO_MONTH  on REPORTS$DSO computed by (DSO_MONTH);
create index IDX_REPORTS_DSO_YEAR  on REPORTS$DSO computed by (DSO_YEAR);

ALTER TABLE FILES$DEBTORS ADD TRAIN_TYPE DOM_REFERENCE;
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('order_c', 'all', 'Volgorde', 'Ordre', 'Order', 0)#
ALTER TABLE TRAIN ADD ORDER_CYCLE INTEGER#
create index ix_train_order_cycle on TRAIN computed by (ORDER_CYCLE)#
DELETE FROM TEKSTEN WHERE CODE = 'BASE_TRAIN_TYPE'#
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('BASE_TRAIN_TYPE','all','TRAJECT1','TRAJECT1','TRAJECT1','1')#
UPDATE FILES$DEBTORS SET TRAIN_TYPE='TRAJECT1'#
UPDATE FILES$REFERENCES SET TRAIN_TYPE='TRAJECT1'#
ALTER TABLE REPORTS$SALDO ADD CLIENT_ID DOM_RECORD_ID;
ALTER TABLE REPORTS$SALDO
ADD CONSTRAINT FK_SALDO_CLIENT
FOREIGN KEY (CLIENT_ID) REFERENCES CLIENTS$CLIENTS;
ALTER TABLE REPORTS$DSO ADD CLIENT_ID DOM_RECORD_ID;
ALTER TABLE REPORTS$DSO
ADD CONSTRAINT FK_DSO_CLIENT
FOREIGN KEY (CLIENT_ID) REFERENCES CLIENTS$CLIENTS;
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('not_due_c', 'all', 'Niet vervallen', 'Non-échu', 'Not due', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('past_due_c', 'all', 'Vervallen', 'Échu', 'Due', 0);
ALTER TABLE FILES$DEBTORS ADD CREDIT_LIMIT DOM_MONEY#
create index ix_debtor_credit_limit on FILES$DEBTORS computed by (CREDIT_LIMIT)#

CREATE TABLE IMPORTED_MAILS (
  IMPORTED_MAIL_ID int not null primary key,
  FILE_ID int not null references FILES$FILES,
  CREATION_DATE TIMESTAMP,
  FROM_EMAIL varchar(50),
  TO_EMAIL varchar(50),
  MAIL_BODY varchar(10000),
  MAIL_SUBJECT varchar(3000)
);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_from_c','all','Van','De','From',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_to_c','all','Aan','À','To',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_mails_found_c','all','Geen e-mails gevonden.','Pas d''emails retrouvés','No emails found.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('imported_mails_c','all','Geïmporteerde e-mails','Emails importés','Imported emails',0);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_subject_c','all','Onderwerp','Sujet','Subject',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('menu_imported-mail_overview','all','Geïmporteerde e-mails','Emails importés','Imported emails',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('date_from_c','all','van','de','from',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('date_till_c','all','tot', 'à','until',0);
