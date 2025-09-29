#mo ファイルの作成
msgfmt -o languages/linelogin-ja.mo languages/linelogin-ja.po

#jed jsonファイルの作成
wp i18n make-json languages/linelogin-ja.po --no-purge