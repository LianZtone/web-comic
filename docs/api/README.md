# Web Comick API (Postman)

## File
- `docs/api/web-comick.postman_collection.json`

## Cara pakai
1. Buka Postman.
2. Import file collection `docs/api/web-comick.postman_collection.json`.
3. Set variable `base_url` sesuai URL app kamu. Contoh: `http://127.0.0.1:8000`.
4. Jalankan request `Auth -> Register` atau `Auth -> Login`.
5. Token otomatis tersimpan ke variable collection `token`.
6. Jalankan request di folder `Protected (Bearer Token)`.

## Catatan variabel
- `comic_slug`: slug komik target.
- `chapter_number`: nomor chapter target.
- `comic_comment_id`: id komentar comic (untuk update/delete/vote).
- `chapter_comment_id`: id komentar chapter (untuk update/delete/vote).
- `reader_key`: identitas guest untuk endpoint guest reaction/vote.
