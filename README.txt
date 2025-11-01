# 📦 Paesio — Segnala il tuo Comune — Installazione su Altervista

## ✅ Passaggi rapidi

1. **Crea un sito su Altervista** (es: paesio.altervista.org)
2. **Crea un database MySQL** nella dashboard
3. **Importa lo schema SQL** con phpMyAdmin
4. **Carica i file** tramite FTP o pannello file
5. **Modifica le credenziali DB** in:
   - `submit.php`
   - `admin/index.php`
6. **Crea la cartella `upload/`** (vuota) e imposta permessi a 755 o 777
7. **Apri il sito e testa il form!**

## 🔐 Modifica password admin

Nel file `admin/index.php`, cambia:
```php
$admin_password = 'miasuperpassword';