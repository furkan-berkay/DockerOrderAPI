# Order Discount API
Bu proje, bir sipariş sisteminin RESTful API olarak geliştirilmesini sağlar. 
JSON tabanlı bir veri deposu kullanılarak siparişler oluşturulabilir, 
listelenebilir ve silinebilir. 
Ayrıca siparişlere özel indirim hesaplamaları yapılır.

## Özellikler
- Sipariş ekleme (POST /order)
- Sipariş listeleme (GET /orders)
- Müşteri bazlı sipariş listeleme (GET /orders/{id})
- Sipariş silme (DELETE /order/{id})
- Siparişlere özel indirim hesaplama
- Payload validasyonları bulunmaktadır.
- Stok kontrolleri yapar.
- Müşteri veri setinda var mı kontrolü yapılır.
- Daha fazla indirim türü ve kurallar eklenebilir.
- - Yeni bir indirim eklemek için, sadece yeni bir apply fonksiyonu yazıp applyDiscounts() içinde çağırmanız yeterlidir.


## Kurulum

### Gereksinimler
- PHP 7.4+
- Composer (Opsiyonel, bağımlılık yönetimi için)
- Docker (Opsiyonel, kapsayıcı ortamda çalıştırmak için)

### Çalıştırma
1. Projeyi klonlayın:
```bash
git clone https://github.com/furkan-berkay/DockerOrderAPI.git
cd DockerOrderAPI
```
2. Bağımlılıkları yükleyin (Opsiyonel):
```bash
composer install
```
3. Gerekli JSON dosyalarını oluşturun:
- storage/orders.json → Siparişleri saklamak için.
- storage/products.json → Ürün bilgileri.
- storage/customers.json → Müşteri bilgileri.
- storage/discounts.json → İndirim bilgileri.
4. PHP yerel sunucusunu başlatın
```bash
   php -S localhost:8000 -t public
```
5. Docker ile çalıştırmak için (Opsiyonel):
```bash
   docker-compose up -d
```

## API Kullanımı

### Sipariş Ekleme
Endpoint: POST /order  

Örnek istek:
```json
{
  "customerId": 1,
  "items": [
    {
      "productId": 100,
      "quantity": 10,
      "unitPrice": "120.75"
    },
    {
      "productId": 101,
      "quantity": 4,
      "unitPrice": "49.50"
    },
    {
      "productId": 102,
      "quantity": 7,
      "unitPrice": "49.50"
    }
  ]
}

```
Örnek çıktı:
```json
{
  "id": 5,
  "customerId": 1,
  "items": [
    {
      "productId": 100,
      "quantity": 10,
      "unitPrice": "120.75",
      "total": "1207.5"
    },
    {
      "productId": 101,
      "quantity": 4,
      "unitPrice": "49.50",
      "total": "198"
    },
    {
      "productId": 102,
      "quantity": 7,
      "unitPrice": "49.50",
      "total": "346.5"
    }
  ],
  "total": "1752",
  "discount": {
    "orderId": 5,
    "discounts": [
      {
        "discountReason": "10_PERCENT_OVER_1000",
        "discountAmount": "175.20",
        "subtotal": "1576.80"
      },
      {
        "discountReason": "BUY_5_GET_1_CATEGORY_2",
        "discountAmount": "11.28",
        "subtotal": "1565.52"
      },
      {
        "discountReason": "20_PERCENT_LOWEST_PRODUCT_CATEGORY_1",
        "discountAmount": "9.90",
        "subtotal": "1555.62"
      }
    ],
    "totalDiscount": "196.38",
    "discountedTotal": "1555.62"
  }
}
```
### Sipariş Listeleme
- Endpoint: GET /orders  
Örnek çıktı:
```json
[
  {
    "id": 1,
    "customerId": 1,
    "items": [
      {
        "productId": 102,
        "quantity": 10,
        "unitPrice": "11.28",
        "total": "112.80"
      }
    ],
    "total": "112.80",
    "discount": null
  },
  ...
  {
    "id": 5,
    "customerId": 1,
    "items": [
      {
        "productId": 100,
        "quantity": 10,
        "unitPrice": "120.75",
        "total": "1207.5"
      },
      {
        "productId": 101,
        "quantity": 4,
        "unitPrice": "49.50",
        "total": "198"
      },
      {
        "productId": 102,
        "quantity": 7,
        "unitPrice": "49.50",
        "total": "346.5"
      }
    ],
    "total": "1752",
    "discount": {
      "orderId": 5,
      "discounts": [
        {
          "discountReason": "10_PERCENT_OVER_1000",
          "discountAmount": "175.20",
          "subtotal": "1576.80"
        },
        {
          "discountReason": "BUY_5_GET_1_CATEGORY_2",
          "discountAmount": "11.28",
          "subtotal": "1565.52"
        },
        {
          "discountReason": "20_PERCENT_LOWEST_PRODUCT_CATEGORY_1",
          "discountAmount": "9.90",
          "subtotal": "1555.62"
        }
      ],
      "totalDiscount": "196.38",
      "discountedTotal": "1555.62"
    }
  }
]
```
- Endpoint: GET /orders{customerId}  
  Örnek çıktı:
```json
[
  {
    "id": 1,
    "customerId": 1,
    "items": [
      {
        "productId": 102,
        "quantity": 10,
        "unitPrice": "11.28",
        "total": "112.80"
      }
    ],
    "total": "112.80",
    "discount": null
  },
  {
    "id": 4,
    "customerId": 1,
    "items": [
      {
        "productId": 100,
        "quantity": 10,
        "unitPrice": "120.75",
        "total": "1207.5"
      },
      {
        "productId": 101,
        "quantity": 4,
        "unitPrice": "49.50",
        "total": "198"
      },
      {
        "productId": 102,
        "quantity": 7,
        "unitPrice": "49.50",
        "total": "346.5"
      }
    ],
    "total": "1752",
    "discount": {
      "orderId": 4,
      "discounts": [
        {
          "discountReason": "10_PERCENT_OVER_1000",
          "discountAmount": "175.20",
          "subtotal": "1576.80"
        },
        {
          "discountReason": "BUY_5_GET_1_CATEGORY_2",
          "discountAmount": "11.28",
          "subtotal": "1565.52"
        },
        {
          "discountReason": "20_PERCENT_LOWEST_PRODUCT_CATEGORY_1",
          "discountAmount": "9.90",
          "subtotal": "1555.62"
        }
      ],
      "totalDiscount": "196.38",
      "discountedTotal": "1555.62"
    }
  },
  {
    "id": 5,
    "customerId": 1,
    "items": [
      {
        "productId": 100,
        "quantity": 10,
        "unitPrice": "120.75",
        "total": "1207.5"
      },
      {
        "productId": 101,
        "quantity": 4,
        "unitPrice": "49.50",
        "total": "198"
      },
      {
        "productId": 102,
        "quantity": 7,
        "unitPrice": "49.50",
        "total": "346.5"
      }
    ],
    "total": "1752",
    "discount": {
      "orderId": 5,
      "discounts": [
        {
          "discountReason": "10_PERCENT_OVER_1000",
          "discountAmount": "175.20",
          "subtotal": "1576.80"
        },
        {
          "discountReason": "BUY_5_GET_1_CATEGORY_2",
          "discountAmount": "11.28",
          "subtotal": "1565.52"
        },
        {
          "discountReason": "20_PERCENT_LOWEST_PRODUCT_CATEGORY_1",
          "discountAmount": "9.90",
          "subtotal": "1555.62"
        }
      ],
      "totalDiscount": "196.38",
      "discountedTotal": "1555.62"
    }
  }
]
```

### Sipariş Silme
- Endpoint: DELETE /order/{id}  
  Örnek çıktı:
```json
{
  "message": "Order deleted successfully.Discount data deleted successfully."
}
```

## Yapılabilecekler
- Daha fazla indirim türü eklenebilir
- Daha fazla kural tanımlanabilir
- Gerçek bir veritabanı ile entegrasyonu yapılabilir.
- product ve customer için CRUD yapılabilir.
- Tüm verilere status değeri eklenebilir, böylece delete ve hard delete ayrımı yapılabilir.
