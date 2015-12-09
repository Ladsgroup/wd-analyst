from core import DumpReader
import pymysql
import sys
dump = DumpReader('/public/dumps/public/wikidatawiki/entities/20151130/wikidata-20151130-all.json.bz2', 20000000)
data = {}
"""
CREATE TABLE property
(
property INT(15) NOT NULL,
value INT(15) NOT NULL,
no_item INT(15) NOT NULL,
no_uniq_items INT(15) NOT NULL,
no_labels INT(15) NOT NULL,
no_site_links INT(15) NOT NULL,
no_descriptions INT(15) NOT NULL,
no_claims INT(15) NOT NULL,
no_qua INT(15) NOT NULL,
no_ref INT(15) NOT NULL,
no_wiki_ref INT(15) NOT NULL
);
"""

for item_content in dump.run():
    if item_content['type'] != 'item':
        continue
    no_labels = len(item_content.get('labels', {}))
    no_site_links = len(item_content.get('sitelinks', {}))
    no_descriptions = len(item_content.get('descriptions', {}))
    no_claims = 0
    for pid in item_content.get('claims', {}):
        no_claims += len(item_content['claims'][pid])
    for pid in item_content.get('claims', {}):
        claims = item_content['claims'][pid]
        pid_int = int(pid.split('P')[1])
        uniqe_added = False
        for claim in claims:
            no_qua = len(claim.get('qualifiers', []))
            no_refs = len(claim.get('references', []))
            no_wiki_ref = 0
            for ref in claim.get('references', []):
                if 'P143' in ref.get('snaks', {}):
                    no_wiki_ref += 1
            data_to_add = [1, 0, no_labels, no_site_links, no_descriptions, no_claims, no_qua, no_refs, no_wiki_ref]
            old_data = data.get((pid_int, 0), [0] * len(data_to_add))
            if not uniqe_added:
                uniqe_added = True
                data_to_add[1] = 1
            new_data = [data_to_add[i] + old_data[i] for i in range(len(data_to_add))]
            data[(pid_int, 0)] = new_data
            if pid_int not in [31, 17, 21, 27, 131, 105, 106, 19, 20, 641, 136, 495, 50, 57, 170, 161]:
                continue
            try:
                val = claim['mainsnak']['datavalue']['value']['numeric-id']
            except KeyError:
                pass
            except TypeError:
                pass
            else:
                old_data = data.get((pid_int, val), [0] * len(data_to_add))
                new_data = [data_to_add[i] + old_data[i] for i in range(len(data_to_add))]
                data[(pid_int, val)] = new_data

db = pymysql.connect(host="tools-db", db="s52781__wd_p",
                     read_default_file="~/replica.my.cnf")
cursor = db.cursor()

for case in data:
    val = data[case]
    insert_statement = (
        "INSERT INTO property "
        "(property, value, no_item, no_uniq_items, no_labels, no_site_links, no_descriptions, no_claims, no_qua, no_ref, no_wiki_ref) "
        "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)")
    cursor.execute(insert_statement,
                    (case[0], case[1], val[0], val[1], val[2], val[3], val[4], val[5], val[6], val[7], val[8]))
db.commit()
cursor.close()
db.close()

