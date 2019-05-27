from bs4 import BeautifulSoup
import requests
import sys
import json
import datetime

def storeProduct(data):
    #URL = "https://descuentos.insive.cl/api/product"
    URL = "http://localhost/descuentos/api/product.php"
  
    # defining a params dict for the parameters to be sent to the API 
    PARAMS = data
    
    # sending get request and saving the response as response object 
    r = requests.post(url = URL, params = PARAMS) 
      
    # extracting data in json format 
    response = r.text

    #print(response)


def searchOffer(pageNumber):
    products = []
    html_doc = requests.get("https://www.spdigital.cl/categories/search/page:" + str(pageNumber) + "?ext=html&q=%40offers&category_id=-1", headers = {'Accept-Encoding' : 'identity'})
    soup = BeautifulSoup(html_doc.content, 'html.parser')
    index = 0

    for item in soup.find_all('div', attrs={'class': 'product-item-mosaic'}):
        percentageIndex = item.text.find('%')

        if (percentageIndex != -1):
            percentageValue = int(item.text[percentageIndex-4:percentageIndex])
            
            data = {}
            data['code'] = item.find('a').get('href').split("/")[-1]
            data['name'] = item.find('div', attrs = {'class': 'name'}).text
            data['url'] = 'http://www.spdigital.cl' + item.find('a').get('href')
            data['image'] = item.find('img', attrs = {'class': 'small-image'}).get('src')
            data['discount'] = str(percentageValue)
            data['previous_price'] = item.find('span', attrs = {'class': 'cash-previous-price-value'}).text.replace(".", "").replace("$", "")
            data['offer_price'] = item.find('div', attrs = {'class': 'cash-price'}).text.replace(".", "").replace("$", "")
            data['datetime'] = str(datetime.datetime.now())
            products.append(data)
            storeProduct(data)
            index += 1

    json_data = json.dumps(products)
    return json_data


########################################################
####################### MAIN ###########################
########################################################
params = len(sys.argv)

if params == 1:
    results = open("products.txt", "w")
    for i in range(1, 115):
        print("Fetch Page " + str(i))
        data = searchOffer(i)
        results.write(data)
        results.write("\n")
    results.close()

elif params == 2:
    data = searchOffer(sys.argv[1])
    results = open("products.txt", "a")
    results.write(data)
    results.write("\n")
    results.close()

else:
    print("Error!!!")
