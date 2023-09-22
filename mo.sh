#mo ファイルの作成
msgfmt -o languages/linelogin-ja.mo languages/linelogin-ja.po

#jed jsonファイルの作成
docker compose run --rm cli wp i18n make-json wp-content/plugins/linelogin/languages/linelogin-ja.po --no-purge