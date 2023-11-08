import requests
from bs4 import BeautifulSoup
import urllib
import mysql.connector

# 데이터베이스 연결 설정
cnx = mysql.connector.connect(user='root', password='', host='localhost', database='englishww')
cursor = cnx.cursor()

# 이미지를 다운로드할 디렉토리 경로 설정
download_directory = "/your/image/directory/path/"

# words 테이블에서 imgdr가 NULL인 단어 가져오기
select_query = "SELECT wordeng FROM word WHERE imgdr IS NULL"
cursor.execute(select_query)
words_to_download = cursor.fetchall()

for word in words_to_download:
    search_query = word[0]  # 단어를 이미지 검색 쿼리로 사용

    # 검색어와 관련된 이미지 검색 페이지 URL
    search_url = f"https://www.freepik.com/search?format=search&query={search_query}&type=vector"

    # 웹 페이지 내용을 가져오기
    response = requests.get(search_url)
    soup = BeautifulSoup(response.content, "html.parser")

    # 이미지 검색 결과 중 첫 번째 이미지 URL 가져오기
    image_div = soup.find("div", class_="img-list")
    
    if image_div:
        image_url = image_div.find("img")["src"]

        # 이미지 다운로드
        image_filename = f"{search_query}_image.jpg"  # 이미지 파일 이름 설정
        full_image_path = download_directory + image_filename  # 이미지를 저장할 전체 경로
        urllib.request.urlretrieve(image_url, full_image_path)

        # 이미지 저장 경로를 데이터베이스에 업데이트
        update_query = f"UPDATE word SET imgdr = '{full_image_path}' WHERE wordeng = '{search_query}'"
        cursor.execute(update_query)
        cnx.commit()
        print(f"이미지 다운로드 및 업데이트 완료: {search_query}")
    else:
        print(f"이미지를 찾을 수 없습니다: {search_query}")

# 연결 종료
cursor.close()
cnx.close()
