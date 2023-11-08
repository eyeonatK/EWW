import mysql.connector
import pandas as pd

# 데이터베이스 연결 설정
cnx = mysql.connector.connect(user='root', password='', host='localhost', database='englishww')
cursor = cnx.cursor()

# 단어 및 예문을 담을 데이터프레임 생성
words_df = pd.read_excel(r'C:/xampp/htdocs/wwww/단어예문.xlsx')

# words 테이블에서 가장 큰 word_id 값 가져오기
cursor.execute("SELECT MAX(word_id) FROM word")
max_num = cursor.fetchone()[0]

# num 값 초기화
if max_num is None:
    max_num = 0

# 단어, 예문, 뜻을 순회하면서 데이터베이스에 추가
for index, row in words_df.iterrows():
    english = str(row['단어'])
    korean = str(row['뜻']) if pd.notna(row['뜻']) else ""  # 만약 뜻이 없다면 빈 문자열 할당
    example = str(row["예문"]) if pd.notna(row["예문"]) else ""
    examplekr = str(row["한국어"]) if pd.notna(row["한국어"]) else ""
    # 단어 중복 검사
    check_query = "SELECT word_id FROM word WHERE english = '{}'".format(english)
    cursor.execute(check_query)
    existing_word = cursor.fetchone()
    
    if existing_word is None:
        # 단어, 뜻, 예문, word_id 추가
        max_num += 1
        # 따옴표 이스케이프 처리 적용
        english = english.replace("'", "''")
        korean = korean.replace("'", "''")
        example = example.replace("'", "''")
        examplekr = examplekr.replace("'", "''")

        insert_query = "INSERT INTO word (english, korean, exeng, exkor, word_id, difficulty) VALUES ('{}', '{}', '{}', '{}', {}, 0)".format(english, korean, example, examplekr, max_num)
        cursor.execute(insert_query)
        cnx.commit()
    else:
        print("이미 존재하는 단어입니다:", english)
        
        update_parts = []
        params = []

        if korean:
            update_parts.append("korean = %s")
            params.append(korean)
        if example:
            update_parts.append("exeng = %s")
            params.append(example)
        if examplekr:
            update_parts.append("exkor = %s")
            params.append(examplekr)

        if update_parts:
            update_query = "UPDATE word SET " + ", ".join(update_parts) + " WHERE english = %s"
            params.append(english)
            cursor.execute(update_query, params)
            cnx.commit()

# 연결 종료
cursor.close()
cnx.close()
