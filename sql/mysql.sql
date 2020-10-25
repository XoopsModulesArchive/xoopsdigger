CREATE TABLE xd_document (
    id         MEDIUMINT(9) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    modname    VARCHAR(60)    NOT NULL,
    date       INT(10) UNSIGNED        DEFAULT '0',
    link       VARCHAR(255)   NOT NULL,
    title      VARCHAR(255)   NOT NULL,
    doc_weight FLOAT UNSIGNED NOT NULL DEFAULT '1',
    UNIQUE (link)

);
CREATE TABLE xd_keyword (
    id        MEDIUMINT(9) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    word      VARCHAR(60) NOT NULL,
    twoletter CHAR(2)     NOT NULL,
    INDEX (twoletter),
    UNIQUE (word),
    INDEX (word)
);
CREATE TABLE xd_engine (
    doc_id  INT(9) UNSIGNED,
    word_id MEDIUMINT(9) UNSIGNED,
    weight  FLOAT UNSIGNED,
    UNIQUE (doc_id, word_id),
    INDEX (doc_id)
);
CREATE TABLE xd_word_log (
    word  VARCHAR(60) PRIMARY KEY,
    count MEDIUMINT(8) DEFAULT '1'
);
