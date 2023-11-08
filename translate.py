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
import pyperclip

# 로깅 설정
logging.basicConfig(filename='C:/xampp/htdocs/wwww/app.log', level=logging.INFO)
logging.basicConfig(filename='C:/xampp/htdocs/wwww/vrtest.log', level=logging.ERROR, format='%(asctime)s - %(levelname)s - %(message)s')

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

driver.get("https://translate.google.com/")
logging.info(f"이동완료")

try:
    logging.info(f"try진입")
    select_query = "SELECT english FROM word WHERE korean IS NULL OR korean = '';"
    cursor.execute(select_query)
    words_to_translate = cursor.fetchall()

    if not words_to_translate:
        logging.error("번역할 단어가 데이터베이스에 없습니다.")
        driver.quit()
        cursor.close()
        cnx.close()
        exit()

    for english_word in words_to_translate:
        english_word = english_word[0]
        pyperclip.copy(english_word)
        logging.info(f"처리 중인 단어: {english_word}")

        try:
            try:
            # 처음 번역된 텍스트를 가져옵니다.
                translated_text_element = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '...')))
                pretext = translated_text_element.text  # 이전에 번역된 텍스트 추출
            except Exception as e:
                logging.error(f"이전에 번역된 텍스트를 추출 중 에러 발생: {e}")
                pretext = ""  # pretext를 빈 문자열로 설정
            
            input_box = WebDriverWait(driver, 10).until(EC.element_to_be_clickable((By.CSS_SELECTOR, '#yDmH0d > c-wiz > div > div.ToWKne > c-wiz > div.OlSOob > c-wiz > div.ccvoYb > div.AxqVh > div.OPPzxe > c-wiz.rm1UF.UnxENd > span > span > div > textarea')))
            input_box.clear()
            input_box.send_keys(pyperclip.paste())
            time.sleep(1)
            # 이전 번역된 텍스트와 새로운 번역된 텍스트가 동일한지 확인합니다.
            retry_count = 0
            while True:
                time.sleep(1)  # 1초 대기
                translated_text = driver.find_element(By.CSS_SELECTOR,'#yDmH0d > c-wiz > div > div.ToWKne > c-wiz > div.OlSOob > c-wiz > div.ccvoYb > div.AxqVh > div.OPPzxe > c-wiz.sciAJc > div > div.usGWQd > div > div.lRu31 > span.HwtZe > span > span').text
                if translated_text != pretext or retry_count > 10:  # 탈출 조건 추가
                    break
                retry_count += 1
                

            translated_text_element = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, '#yDmH0d > c-wiz > div > div.ToWKne > c-wiz > div.OlSOob > c-wiz > div.ccvoYb > div.AxqVh > div.OPPzxe > c-wiz.sciAJc > div > div.usGWQd > div > div.lRu31 > span.HwtZe > span > span')))
            translated_text = translated_text_element.text  # 번역된 텍스트 추출
            logging.info(f"번역된 텍스트: {translated_text}")
            pretext = translated_text

            update_query = "UPDATE word SET korean = %s WHERE english = %s"
            cursor.execute(update_query, (translated_text, english_word))
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
