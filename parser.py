import configparser
import csv
import os
from datetime import datetime
from dateutil import relativedelta
import logging
import mysql.connector
from mysql.connector import errorcode

PAPERCUT_PREFIX = "papercut-print-log"
PAPERCUT_EXT = "csv"
PAPERCUT_DELIMITER = ","
PAPERCUT_DATE_FORMAT = "%Y-%m-%d"

CONFIG_FILE_NAME = "config.ini"
LOG_LEVEL = "DEBUG"
LOG_FOLDER = "/var/log/papercut_parser"


def get_parameter(conf, parameter):
    def is_param_exists() -> bool:
        try:
            _ = conf[parameter]
            return True
        except:
            return False

    if is_param_exists():
        return conf[parameter]


def configure_logger():
    if LOG_LEVEL in logging._nameToLevel:
        level = logging._nameToLevel.get(LOG_LEVEL)
        logger = logging.getLogger()
        logger.setLevel(level)
        # fh = logging.FileHandler(f'{LOG_FOLDER}/main.log')
        # fh.setLevel(level)
        ch = logging.StreamHandler()
        ch.setLevel(level)
        formatter = logging.Formatter(
            '%(asctime)s [%(filename)s.%(lineno)d] %(processName)s %(levelname)-1s %(name)s - %(message)s')
        ch.setFormatter(formatter)
        # fh.setFormatter(formatter)
        logger.addHandler(ch)
        # logger.addHandler(fh)
    else:
        raise Exception(f"Can't recognize log level: {LOG_LEVEL}")


def read_app_config(conf_file):
    logging.info("Using configuration file - %s", config_file)
    if os.path.isfile(conf_file):
        config = configparser.ConfigParser()
        config.read(conf_file)
        return config["main"]
    else:
        logging.error(f"Can't find config file {conf_file}")
        return None


def get_config_file_name():
    return get_script_path() + "/" + CONFIG_FILE_NAME


def get_script_path():
    return os.path.dirname(os.path.realpath(__file__))


def get_yesterday():
    return (datetime.now() + relativedelta.relativedelta(days=-1)).strftime(PAPERCUT_DATE_FORMAT)


def make_file_name():
    return f"{PAPERCUT_LOG_DIR}/{PAPERCUT_PREFIX}-{get_yesterday()}.{PAPERCUT_EXT}"


def read_file(papercut_log):
    try:
        f = open(papercut_log)
        lines = f.readlines()[2:]
        return csv.reader(lines, delimiter=",")
    except FileNotFoundError:
        logging.exception("File %s not found", papercut_log)
        exit(11)


def make_sql_values(data):
    sql_values = []
    for line in data:
        sql_values.append(f"("
                          f"'{line[0]}',"  # Time, datetime
                          f"'{line[1]}',"  # User, text
                          f"{line[2] or 0},"  # Pages, int
                          f"{line[3] or 0},"  # Copies, int
                          f"'{line[4]}',"  # Printer, text
                          f"'{line[5]}',"  # Document name, text
                          f"'{line[6]}',"  # Client, text
                          f"'{line[7]}',"  # Paper Size, text
                          f"'{line[8]}',"  # Language, text
                          f"'{line[9] or 0}',"  # Height, int
                          f"'{line[10] or 0}',"  # Width, int
                          f"'{line[11]}',"  # Duplex, text
                          f"'{line[12]}',"  # Grayscale, text
                          f"'{line[13]}'"  # Size, text
                          f")")
    logging.debug("Parsed values - %s", sql_values)
    return sql_values


def make_sql_query(data):
    sql_values = (",".join([x for x in data]))
    _sql = f"INSERT INTO {TABLE_NAME} (" \
           f"time, " \
           f"user, " \
           f"pages, " \
           f"copies, " \
           f"printer, " \
           f"document_name, " \
           f"client, " \
           f"paper_size, " \
           f"language, " \
           f"height, " \
           f"width, " \
           f"duplex, " \
           f"grayscale, " \
           f"size) " \
           f"VALUES {sql_values}"
    logging.debug("Generated SQL - %s", _sql)
    return _sql


def open_connect_with_db(conf):
    try:
        return mysql.connector.connect(user=conf["db_user"],
                                       password=conf["db_pass"],
                                       host=conf["db_host"],
                                       database=conf["db_name"],
                                       port=conf["db_port"])
    except mysql.connector.Error as err:
        if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
            logging.error("Something is wrong with your user name or password")
        elif err.errno == errorcode.ER_BAD_DB_ERROR:
            logging.error("Database does not exist")
        else:
            logging.error(err)
        return None


def execute_query(connection, data):
    if connection is not None and data is not None:
        try:
            cursor = connection.cursor()
            cursor.execute(data)
            connection.commit()
            cursor.close()
        except mysql.connector.Error as err:
            if err.errno == errorcode.ER_ACCESS_DENIED_ERROR:
                logging.error("Access denied")
            else:
                logging.error(err.msg)


def close_connection(connection):
    if connection is not None:
        connection.close()


def insert_data_to_db(conf, data: str):
    connection = open_connect_with_db(conf=conf)
    execute_query(connection, data)
    close_connection(connection)


if __name__ == '__main__':
    configure_logger()
    config_file = get_config_file_name()
    config = read_app_config(config_file)

    PAPERCUT_LOG_DIR = get_parameter(conf=config, parameter="papercut_dir")

    TABLE_NAME = "papercut_log"

    FILE_NAME = make_file_name()
    if not os.path.isfile(FILE_NAME):
        logging.error("Can't find PaperCut log file - %s", FILE_NAME)
    lines = read_file(FILE_NAME)
    values = make_sql_values(lines)
    sql = make_sql_query(values)
    insert_data_to_db(conf=config, data=sql)
