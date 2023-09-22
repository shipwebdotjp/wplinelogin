source ./.env
find . -type d \( -name 'node_modules' -o -name 'src' -o -name 'vendor' \) -prune -o \( -type f \( -name '*.php' -or -name '*.js' \) \) -print > list
xgettext -k"__" -k"_e" -k"_n" -o languages/linelogin.pot --files-from=list --from-code=UTF-8 --copyright-holder=SHIP --package-name='WP LINE Login' --package-version=${LINE_CONNECT_VERSION} --msgid-bugs-address=shipwebdotjp@gmail.com
#初回はmsginit
#msginit --locale=ja_JP.UTF-8 --input=languages/linelogin.pot --output=languages/linelogin-ja.po --no-translator

#2回目からはmsgmerge
msgmerge --backup=simple --suffix='.bak' --update languages/linelogin-ja.po languages/linelogin.pot
