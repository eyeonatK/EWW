import mysql.connector
import re

with open('C:/xampp/htdocs/wwww/water.txt', 'r', encoding='utf-8') as file:
    data = file.read()

conn = mysql.connector.connect(user='root', password='', host='localhost', database='dictionary')
cursor = conn.cursor()

cursor.execute('''
CREATE TABLE IF NOT EXISTS words (
    id INTEGER AUTO_INCREMENT PRIMARY KEY,
    word TEXT,
    pos TEXT,
    pro TEXT,
    meaning TEXT,
    example TEXT
)
''')

def clean_text(text):
    # <와 > 사이의 문자들을 제거
    clean = re.sub(r'<.*?>', '', text)
    # &lt;와 &gt;를 각각 <와 >로 변환
    clean = clean.replace('&lt;', '<').replace('&gt;', '>')
    return clean.strip()

entries = re.split(r'<FONT color="#880055"', data)[1:]
processed_combinations = set()  # 단어와 뜻의 조합을 저장할 집합

for entry in entries:
    word = re.search(r'>(.*?)</FONT>', entry).group(1)
    
    pronunciation_pattern = r'<b>(.*?)</b>'
    pronunciation_match = re.search(pronunciation_pattern, entry)
    pronunciation = pronunciation_match.group(1) if pronunciation_match else ""

    pos_pattern = r'<font color=#008080><b>([a-zA-Z.]+)</b></font>'
    pos_match = re.search(pos_pattern, entry)
    if pos_match:
        pos = pos_match.group(1)
    else:
        pos = None
    
    meaning_sections = re.split(r'<font color=#FF0000>\d+</font>', entry)[1:]
    

    for index, section in enumerate(meaning_sections):
        # 이미지 태그 제거
        section = re.sub(r'<img.*?>', '', section)
        # 첫 번째 섹션이 아닐 때만 품사 추출
        if index > 0:
            pos_match = re.search(pos_pattern, section)
            if pos_match:
                pos = pos_match.group(1)
            else:
                pos = None

        # 뜻 추출
        meaning_parts = section.split('<br>')
        if not meaning_parts[0].strip() or meaning_parts[0].strip() == "<font color=#008080>":  # meaning이 빈 문자열이거나 해당 태그만 있는 경우
            meaning = meaning_parts[1].strip()
        else:
            meaning = meaning_parts[0].strip()

        if meaning.startswith('<font color=#008080><b>'):
            meaning = meaning + " " + meaning_parts[2].strip() if len(meaning_parts) > 2 else meaning

        meaning = clean_text(meaning)
        print("문제가 발생하는 섹션:", word, meaning)


        