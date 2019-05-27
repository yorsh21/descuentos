from bs4 import BeautifulSoup
import requests
import sys
import json

def storeProduct(data):
    URL = "http://localhost/descuentos/api/product.php"
  
    # defining a params dict for the parameters to be sent to the API 
    PARAMS = data
    
    # sending get request and saving the response as response object 
    r = requests.post(url = URL, params = PARAMS) 
      
    # extracting data in json format 
    response = r.text

    print(response)


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
            data['productName'] = item.find('div', attrs = {'class': 'name'}).text
            data['productLink'] = 'http://www.spdigital.cl' + item.find('a').get('href')
            data['productImage'] = item.find('img', attrs = {'class': 'small-image'}).get('src')
            data['productPercentage'] = str(percentageValue)
            data['previousPrice'] = item.find('span', attrs = {'class': 'cash-previous-price-value'}).text.replace(".", "").replace("$", "")
            data['offerPrice'] = item.find('div', attrs = {'class': 'cash-price'}).text.replace(".", "").replace("$", "")
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
    for i in range(1, 115):
        data = searchOffer(i)
        results = open("products.txt", "a")
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
