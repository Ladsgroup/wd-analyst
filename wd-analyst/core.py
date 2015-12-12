import bz2
import json
import pymysql

class DumpReader(object):
    """docstring for dump_reader"""
    def __init__(self, name, max_number=None, callback=None):
        self.name = name
        self.max_number = max_number
        self.callback = callback

    def run(self):
        c = 0
        dump = bz2.BZ2File(self.name, 'r')
        for line in dump:
            c += 1
            if self.max_number and c > self.max_number:
                break
            try:
                item_content = json.loads(line.decode()[:-2])
            except ValueError:
                continue
            yield item_content


class DatabaseHandler(object):
    """docstring for DatabaseHandler"""
    def __init__(self, table):
        super(DatabaseHandler, self).__init__()
        self.table = table
        self.host = "tools-db"
        self.db_name = "s52781__wd_p"
        self.config_file = "~/replica.my.cnf"

    def connect(self):
        self.db = pymysql.connect(host=self.host, db=self.db_name,
                             read_default_file=self.config_file)
        self.cursor = db.cursor()

    def finalize(self):
        self.db.commit()
        self.cursor.close()
        self.db.close()
