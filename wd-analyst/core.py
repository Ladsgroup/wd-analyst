import bz2
import json

class DumpReader(object):
    """docstring for dump_reader"""
    def __init__(self, name, max_number, callback=None):
        self.name = name
        self.max_number = max_number
        self.callback = callback

    def run(self):
        c = 0
        dump = bz2.BZ2File(self.name, 'r')
        for line in dump:
            c += 1
            if c > self.max_number:
                break
            try:
                item_content = json.loads(line.decode()[:-2])
            except ValueError:
                continue
            yield item_content

