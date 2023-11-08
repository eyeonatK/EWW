import mysql.connector
import time
import os
import logging
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import Select
from selenium.webdriver.support.select import Select
from selenium.webdriver import Chrome
import pyperclip
from selenium.webdriver.chrome.options import Options
import shutil

chrome_options = Options()
chrome_options.add_experimental_option('prefs',  {
    "download.default_directory": '/path/to/download/directory',  # 다운로드 경로 지정
    "download.prompt_for_download": False,  # 다운로드시 팝업창 비활성화
    "download.directory_upgrade": True,
    "plugins.always_open_pdf_externally": True  # PDF 파일 다운로드시 브라우저 뷰어 비활성화
    }
)

driver = webdriver.Chrome(options=chrome_options)
try:
    cnx = mysql.connector.connect(user='root', password='', host='localhost', database='englishww')
    cursor = cnx.cursor()
except Exception as e:
    logging.error(f"데이터베이스 연결 에러: {e}")
    exit()

driver_path = 'C:/Users/tony/Documents/chromedriver_win32/chromedriver.exe'

try:
    service = Service(driver_path)
    driver = Chrome(service=service)
except Exception as e:
    logging.error(f"드라이버 로드 에러: {e}")
    cursor.close()
    cnx.close()
    exit()

driver.get("https://ttsfree.com/ko")
logging.info(f"이동완료")
time.sleep(10)

try:
    logging.info(f"try진입")
    select_query = "SELECT english FROM word WHERE voicedr IS NULL OR voicedr = '';"
    cursor.execute(select_query)
    words_to_translate = cursor.fetchall()

    if not words_to_translate:
        logging.error("음성변환할 단어가 데이터베이스에 없습니다.")
        driver.quit()
        cursor.close()
        cnx.close()
        exit()
    
    select_element = WebDriverWait(driver, 14).until(
            EC.element_to_be_clickable((By.ID, 'select2-select_lang_bin-container'))
        )
    select = Select(select_element)
    select.select_by_value("50")

    element = driver.find_element(By.ID, 'radioPrimaryen-US')
    # 요소를 클릭하여 선택
    element.click()

    for english_word in words_to_translate:
        english_word = english_word[0]
        pyperclip.copy(english_word)
        logging.info(f"처리 중인 단어: {english_word}")

        try:
            input_box = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.ID, 'input_text')))
            input_box.clear()
            input_box.send_keys(pyperclip.paste())
            time.sleep(1)

            # class와 title 속성을 사용하여 요소 찾기
            button = driver.find_element(By.XPATH, '//a[@class="btn mb-2 lg action-1 text-white convert-now" and @title="Convert now"]')

            # 요소를 클릭하여 선택
            button.click()
            time.sleep(5)
            # 텍스트를 사용하여 요소 찾기
            downbutton = driver.find_element(By.XPATH, '//a[contains(text(), "Download Mp3")]')

            # 요소를 클릭하여 선택
            downbutton.click()

            # 다운로드가 완료될 때까지 기다림 (실제 환경에서는 시간을 조절해야 함)
            time.sleep(5)

            # 파일 이동 및 이름 변경
            old_path = 'C:/Users/tony/Downloads/mp3-output-ttsfree(dot)com.mp3'
            new_path = f'C:/xampp/htdocs/wwww/voice/{english_word}.mp3'
            shutil.move(old_path, new_path)

            # SQL 쿼리 업데이트
            update_query = "UPDATE word SET voicedr = %s WHERE english = %s"
            cursor.execute(update_query, (new_path, english_word))
            cnx.commit()

        except Exception as e:
            logging.error(f"단어 {english_word} 처리 중 에러: {e}")
            continue

except Exception as e:
    logging.error(f"전체 예외 발생: {e}")

finally:
    driver.quit()
    cursor.close()
    cnx.close()
