from core import DumpReader, DatabaseHandler

import sys
dump = DumpReader('/data/project/wd-analyst/wikidata-20151207-all.json.bz2', 100)
data = {}


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
        for claim in claims:
            uniqe_added = False
            no_qua = len(claim.get('qualifiers', []))
            no_refs = len(claim.get('references', []))
            for ref in claim.get('references', []):
                for snak in ref.get('snaks', []):
                    ref_pid = int(snak.split('P')[1])
                    data_to_add = [1, no_labels, no_site_links, no_descriptions, no_claims, no_qua, no_refs]

                    old_data = data.get((ref_pid, 0, 0), [0] * len(data_to_add))
                    new_data = [data_to_add[i] + old_data[i] for i in range(len(data_to_add))]
                    data[(ref_pid, 0, 0)] = new_data

                    old_data = data.get((ref_pid, pid_int, 0), [0] * len(data_to_add))
                    new_data = [data_to_add[i] + old_data[i] for i in range(len(data_to_add))]
                    data[(ref_pid, pid_int, 0)] = new_data

                    for val in ref['snaks'][snak]:
                        try:
                            val = val['datavalue']['value']['numeric-id']
                        except KeyError:
                            pass
                        except TypeError:
                            pass
                        else:
                            old_data = data.get((ref_pid, pid_int, val), [0] * len(data_to_add))
                            new_data = [data_to_add[i] + old_data[i] for i in range(len(data_to_add))]
                            data[(ref_pid, pid_int, val)] = new_data

                            old_data = data.get((ref_pid, 0, val), [0] * len(data_to_add))
                            new_data = [data_to_add[i] + old_data[i] for i in range(len(data_to_add))]
                            data[(ref_pid, 0, val)] = new_data

db_handler = DatabaseHandler('ref')

db_handler.connect()
db_handler.cursor.execute('DROP TABLE ref;')
db_handler.finalize()

sql_query = """
CREATE TABLE ref
(
ref_property INT(15) NOT NULL,
claim_property INT(15) NOT NULL,
value INT(15) NOT NULL,
no_item INT(15) NOT NULL,
no_labels INT(15) NOT NULL,
no_site_links INT(15) NOT NULL,
no_descriptions INT(15) NOT NULL,
no_claims INT(15) NOT NULL,
no_qua INT(15) NOT NULL,
no_ref INT(15) NOT NULL
);
"""
db_handler.connect()
db_handler.cursor.execute(sql_query)
db_handler.finalize()

db_handler.connect()
for case in data:
    val = data[case]
    insert_statement = (
        "INSERT INTO ref "
        "(ref_property, claim_property, value, no_item, no_labels, "
        "no_site_links, no_descriptions, no_claims, no_qua, no_ref) "
        "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)")
    db_handler.cursor.execute(insert_statement, tuple(case) + tuple(data[case]))

db_handler.finalize()
