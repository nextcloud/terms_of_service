# 📜 Terms of service

> ![](https://raw.githubusercontent.com/nextcloud/terms_of_service/master/docs/popup-dialog.png)

Requires users to accept terms of service before accessing data. Text and languages are configurable on the administration panel.

## 🔗 Display on public shares

The setting applies to shares via link or mail (with and without password protection). Default is off `0`
```
./occ config:app:set terms_of_service tos_on_public_shares --value '1'
```

## 🏗️ Development setup

1. Clone the repository
2. Setup your environment: `make`
3. Start contributing 🎉
