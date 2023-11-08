import mysql.connector
import time
import os
import logging
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from selenium.webdriver import Chrome

# 로깅 설정 개선
logging.basicConfig(filename='app.log', level=logging.ERROR, format='%(asctime)s - %(levelname)s - %(message)s')

# 데이터베이스 연결 설정
try:
    cnx = mysql.connector.connect(user='root', password='', host='localhost', database='englishww')
    cursor = cnx.cursor()
except Exception as e:
    logging.error(f"데이터베이스 연결 에러: {e}")
    exit()

# 크롬 드라이버 경로 설정 및 실행
driver_path = 'C:/Users/tony/Documents/chromedriver_win32/chromedriver.exe'

try:
    service = Service(driver_path)
    driver = Chrome(service=service)
except Exception as e:
    logging.error(f"드라이버 로드 에러: {e}")
    cursor.close()
    cnx.close()
    exit()

# Google 번역 웹사이트로 이동
driver.get("https://translate.google.com/")

try:
    select_query = "SELECT english FROM word WHERE voicedr IS NULL"
    cursor.execute(select_query)
    words_to_translate = cursor.fetchall()
    
    for english_word in words_to_translate:
        english_word = english_word[0]
        logging.info(f"처리 중인 단어: {english_word}")

        try:
            input_box = WebDriverWait(driver, 10).until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, 'textarea'))
            )
        except Exception as input_error:
            logging.error(f'입력란을 찾을 수 없습니다. {input_error}')
            continue
        
        input_box.clear()
        input_box.send_keys(english_word)
        
        time.sleep(2)  # 필요에 따라 대기 시간 조절

        try:
            audio_button = driver.find_element(By.CSS_SELECTOR, 'button[jsname="W297wb"]')
            audio_button.click()
        except Exception as audio_error:
            logging.error(f'음성 출력 버튼을 찾을 수 없습니다. {audio_error}')
            continue
        
        time.sleep(2)  # 필요에 따라 대기 시간 조절

        download_path = f'voice/{english_word}.mp3'
        
        try:
            os.rename('C:\\Users\\tony\\Downloads\\translation_tts.mp3', download_path)
        except Exception as file_error:
            logging.error(f'파일을 이동할 수 없습니다. {file_error}')
            continue
        
        logging.info(f'번역 및 음성 다운로드 완료: {english_word}')
        
        update_query = "UPDATE word SET voicedr = %s WHERE english = %s"
        cursor.execute(update_query, (download_path, english_word))
        cnx.commit()

except Exception as e:
    logging.error(f"예외 발생: {e}")

finally:
    driver.quit()
    cursor.close()
    cnx.close()