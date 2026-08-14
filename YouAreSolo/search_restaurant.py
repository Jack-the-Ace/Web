import requests
import folium
from folium.plugins import MiniMap

KAKAO_REST_KEY= 'd50fc61dbf1c193a3081e1faeb17c2b4'

def search_restaurants(keyword):
    url= 'https://dapi.kakao.com/v2/local/search/keyword.json'
    headers= {'Authorization' : f'KakaoAK {KAKAO_REST_KEY}'}

    params= {
        'query' : f'{keyword} 맛집',
        'size' : 5
    }
    response= requests.get(url, headers=headers, params=params)

    if response.status_code !=200:
        print("API 요청 실패:", response.status_code)
        return

    data= response.json()
    places= data.get('documents', [])

    if not places:
        print("검색 결과가 없습니다.. ㅜ_ㅠ ;")
        return

    first_y= float(places[0]['y'])
    first_x= float(places[0]['x'])

    my_map= folium.Map(location=[first_y, first_x], zoom_start=14)
    my_map.add_child(MiniMap())

    print(f"\n TOP 5 [{keyword} 맛집] 검색 결과! : ")
    print("="*90)

    for idx, place in enumerate(places, 1):
        name= place['place_name']
        address= place['road_address_name'] or place['address_name']
        phone= place['phone'] or '전화번호 없음'
        lat= float(place['y'])  #위도
        lng= float(place['x'])  #경도
        place_url= place['place_url']

        print(f"{idx}. {name}")
        print(f" - 주소: {address}")
        print(f" - 전화: {phone}")
        print(f" - URL: {place_url}\n")

        popup_html= f'''
            <div style="width:200px">
                <b>{idx}. {name}</b><br>
                <small>{address}</small><br>
                <small>☎ {phone}</small><br><br>
                <a href="{place_url}" target="_blank">상세정보 보기 ↗</a>
            </div>
        '''

        icon= folium.Icon(color='red', icon='heart', prefix='fa')
        folium.Marker(
            location= [lat,lng],
            tooltip= f"{idx}. {name}",
            popup= folium.Popup(popup_html, max_width=300),
            icon=icon
        ).add_to(my_map)

    my_map.show_in_browser()
    my_map.save('./restaurant_map.html')
    print("지도가 성공적으로 생성되어 브라우저에서 열렸습니다!")

if __name__ == '__main__':
    search_keyword= input("검색할 지역이나 역 이름을 입력하세요! (ex. 강남역, 신림)")
    search_restaurants(search_keyword)