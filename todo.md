# TODO: Handle Deleted Comics Cached in Browser

## Background
Saat ini pengguna masih dapat mengakses halaman komik yang telah dihapus oleh admin karena data atau URL masih tersimpan di browser (cache, bookmark, history, atau local storage).

Akibatnya, ketika pengguna membuka kembali komik tersebut, aplikasi dapat menampilkan error atau halaman kosong.

## Objective
Menampilkan halaman **"Comic Not Found" (404)** ketika komik yang diminta sudah tidak ada di database.

## Requirements

- [ ] Backend mengembalikan response `404 Not Found` apabila comic tidak ditemukan.
- [ ] Response API menggunakan format JSON yang konsisten.
- [ ] Frontend mendeteksi status `404`.
- [ ] Menampilkan halaman khusus **Comic Not Found**.
- [ ] Halaman memiliki tombol **Back to Home** atau **Browse Comics**.
- [ ] Pastikan halaman tidak menampilkan error JavaScript di console.
- [ ] Tangani akses dari:
  - bookmark browser
  - browser history
  - cached page
  - direct URL
  - refresh halaman

## Backend

### API

Contoh endpoint:

```
GET /api/comics/{slug}
```

Jika comic tidak ditemukan:

```http
HTTP/1.1 404 Not Found
```

```json
{
    "message": "Comic not found."
}
```

Laravel:

```php
$comic = Comic::where('slug', $slug)->first();

if (!$comic) {
    return response()->json([
        'message' => 'Comic not found.'
    ], 404);
}
```

Atau menggunakan:

```php
$comic = Comic::where('slug', $slug)->firstOrFail();
```

dan menangani exception sesuai kebutuhan.

---

## Frontend

Saat request comic:

- Jika status `200`
  - tampilkan detail comic.

- Jika status `404`
  - arahkan ke halaman `ComicNotFound`.

Contoh:

```text
GET /comic/one-piece
        │
        ▼
API
        │
        ├── 200 → tampilkan comic
        │
        └── 404 → Comic Not Found
```

---

## Comic Not Found Page

Halaman sebaiknya menampilkan:

- Ilustrasi sederhana
- Judul:
  > Comic Not Found

- Deskripsi:

> The comic you're looking for may have been deleted, is no longer available, or the link is invalid.

Tombol:

- Browse Comics
- Back to Home

---

## Test Cases

- [ ] Membuka comic yang masih ada.
- [ ] Membuka comic yang telah dihapus.
- [ ] Refresh halaman comic yang sudah dihapus.
- [ ] Membuka comic dari bookmark lama.
- [ ] Membuka comic dari browser history.
- [ ] Membuka URL langsung yang tidak valid.
- [ ] Tidak ada error JavaScript setelah menerima response 404.

## Expected Result

Apabila comic sudah dihapus oleh admin namun URL masih tersimpan di browser pengguna, aplikasi tidak mengalami crash maupun halaman kosong. Sebagai gantinya, pengguna akan melihat halaman **Comic Not Found** yang informatif dan dapat kembali menjelajahi komik lain.