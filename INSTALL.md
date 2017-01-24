# TEC-system Installation
---

2016/09/27

<br>

## 必要なソフトウェア
---
* Linux：Red Hat Enterprise Linux 7.2
* Apache 2.4
* PHP 5.4.45,
* PostgreSQL 8
* Smarty 3.1.13

<br>

以下の作業は、すべてroot権限で行ってください。

## １．Apacheのインストール
---

パッケージインストール

    # yum -y install httpd

httpd.confを編集

    # vi /etc/httpd/conf/httpd.conf

ドキュメントルートの変更

```apacheconf
    #DocumentRoot "/var/www/html"
    DocumentRoot "/var/www/html/tecfolio/public_html"
```

Directoryディレクティブの追加

```apacheconf
    <Directory "/var/www/html/tecfolio/public_html">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
```

Filesディレクティブの変更

```apacheconf
    <Files ~ "^\.ht">
        Require all denied
    </Files>
```

tecfolioディレクトリの作成

    # mkdir -p /var/www/html/tecfolio/public_html

logディレクトリの作成

    # mkdir -p /var/www/html/tecfolio/application/log
    # chmod 777 /var/www/html/tecfolio/application/log

Smarty用テンプレートディレクトリの作成

    # mkdir -p /var/www/html/tecfolio/application/smarty/templates_c
    # chmod 777 /var/www/html/tecfolio/application/smarty/templates_c

ApacheのLDAPとSSLモジュールのパッケージインストール
RHEL7.2では、以下のリポジトリについて2か所を**enable=1**にする

`# vi /etc/yum.repos.d/redhat-rhui.repo`

    [rhui-REGION-rhel-server-rhscl]
    name=Red Hat Enterprise Linux Server 7 RHSCL (RPMs)
    mirrorlist=https://rhui2-cds01.REGION.aws.ce.redhat.com/pulp/mirror/content/dist/rhel/rhui/server/7/$releasever/$basearch/rhscl/1/os
    enabled=1

    [rhui-REGION-rhel-server-optional]
    name=Red Hat Enterprise Linux Server 7 Optional (RPMs)
    mirrorlist=https://rhui2-cds01.REGION.aws.ce.redhat.com/pulp/mirror/content/dist/rhel/rhui/server/7/$releasever/$basearch/optional/os
    enabled=1


【確認】つぎの2つのエントリがenableになっていること。

    # yum repolist all

* rhui-REGION-rhel-server-optional/7Server/x86_64
* rhui-REGION-rhel-server-rhscl/7Server/x86_64

LDAPモジュールのパッケージインストール

    # yum -y install mod_ldap

【確認】

    # httpd -M | grep ldap
    ldap_module (shared)
    authnz_ldap_module (shared)
    # ls /usr/lib64/httpd/modules | grep ldap
    mod_authnz_ldap.so
    mod_ldap.so

SSLモジュールのパッケージインストール

    # yum -y install mod_ssl

【確認】

    # httpd -M | grep ssl
    ssl_module (shared)
    # ls /usr/lib64/httpd/modules | grep ssl
    mod_ssl.so

Apacheの起動

    # systemctl start httpd

【確認】複数のhttpデーモンが起動していたらOK

    # ps aux | grep httpd

システム起動時の自動起動設定

    # chkconfig httpd on

<br>

## ２．PHPのインストール
---

**PHPインストール後にApacheを再起動してください。**

パッケージインストール

    # yum -y install php
    # yum -y install php-pgsql
    # yum -y install php-pdo　(phpのバージョンによっては依存関係でインストールされる)
    # yum -y install php-mbstring
    # yum -y install php-ldap

【確認】

    # php -v
    PHP 5.4.16 (cli) (built: Jul 22 2016 04:50:37)
    Copyright (c) 1997-2013 The PHP Group
    Zend Engine v2.4.0, Copyright (c) 1998-2013 Zend Technologies

php.iniの内容を以下のように編集

    # vi /etc/php.ini

    ;;; 以下に書き換え
    memory_limit = 256M
    include_path = "/usr/bin/php"

Apacheの再起動

    # systemctl restart httpd

<br>

## ３．タイムゾーン変更
---

    # timedatectl set-timezone Asia/Tokyo

<br>

## ４．postgresqlのインストール
---

パッケージインストール

    # yum install postgresql-server
    # passwd postgres

ユーザ"postgres"にログイン

    # su - postgres

データベースの初期化

    $ initdb --encoding=UTF8 --no-locale
    (多数メッセージが出る*)

サービス開始

    $ pg_ctl start
    server starting

【確認】

    # ps aux | gre postgres

rootに戻る

    $ exit

システム起動時の自動起動設定

    # chkconfig postgresql on

postgresqlの設定

    # vi /var/lib/pgsql/data/postgresql.conf

    ### 外部からデータベースに接続する場合
    #listen_addresses = 'localhost'
    listen_addresses = '\*'

    ### クライアント認証などのサーバへの接続試行ログ
    #log_connections = off
    log_connections = on

    ### セッション完了ログ
    #log_disconnections = off
    log_disconnections = on

    ### SQL文のログへの記録
    #log_statement = 'none'
    log_statement = 'all'

    ### ロケール設定
    lc_messages = 'en_US.UTF-8'  # locale for system error message strings
    lc_monetary = 'en_US.UTF-8'  # locale for monetary formatting
    lc_numeric  = 'en_US.UTF-8'  # locale for number formatting
    lc_time     = 'en_US.UTF-8'  # locale for time formatting

    ### サーチパスの変更
    #search_path = '"$user",public'         # schema names
    search_path = '"$user",public, worktbl, tecdb, tecfolio'        # schema names

クライアント認証設定

    # vi /var/lib/pgsql/data/pg_hba.conf
      host	all	all	0.0.0.0/0	trust

postgresqlの再起動

    # su - postgres
    $ pgsql restart

<br>

## ５．postgresqlデータ作成
---
dumpデータの復元で必要なテーブルと学生ID、スタッフID、管理者IDがDBに作成されます。

・学生ID    student  PW:student
・スタッフID  staff    PW:staff
・管理者ID   admin   PW:admin

dumpデータの復元
     $ cat tecsystem.dmp | psql tecfoliodb

<br>

## ６．Tec-systemのファイルパス
---
```
/var/www/html
|--tecfolio
|  |--application
|  |  |--configs
|  |  |--controllers
|  |  |--languages
|  |  |--libs
|  |  |--log
|  |  |--models
|  |  |--smarty
|  |  |--views
|  |--library
|  |  |--Smarty
|  |  |--Zend
|  |--public_html
|  |  |--css
|  |  |--favicon.ico
|  |  |--image
|  |  |--images
|  |  |--index.html
|  |  |--js
|  |  |--kwl
|  |  |--lang
|  |  |--pdf
```

<br>

## ７．DBの接続設定
---
- 設定ファイル  
/var/www/html/tecfolio/application/configs/application.ini  

```Ini
    ;// DB接続情報
    dbkwl.adapter		= PDO_Pgsql
    dbkwl.params.host	= "127.0.0.1"
    dbkwl.params.port	= "5432"
    dbkwl.params.username	= "tecfolio"
    dbkwl.params.password	= "tecfolio"
    dbkwl.params.dbname	= "tecfoliodb"
    dbkwl.params.charset	= "UTF8"
```

<br>

## ８．メール送信のためのSMTP設定
---
- 設定ファイル  
/var/www/html/tecfolio/application/libs/ sendmail.php

- 設定例
```php
  // SMTP送信オブジェクト作成  
  public $_param = array(
    'host'	=>'example.ac.jp',
    'port'	=> 25 ,
    'from'	=>'tecsystem@example.ac.jp',
    'protocol'	=>'SMTP',
  );
```
