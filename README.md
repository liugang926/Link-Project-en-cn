# Link Project
 A navigation website developed based on WebStack-Laravel, adding back-end management and Chinese and English options
# 导航网站项目
对WebStack-Laravel导航网站项目增加了后台管理，并支撑多语言
# 安装配置说明
## 1.	环境要求
LNMP，参考（CentOS9/nginx/mysql8.0/PHP8.2）,CentOS9为最小安装；
## 2.	安装步骤
	创建数据库webstack_db，导入webstack_db.sql到数据库中（网上搜索教程）；
	修改db_setup.php中的数据库密码部分，用于网站连接数据库；
	启动nginx服务/启动php-fpm服务；
## 3.	网站说明
	Http://localhost/admin.php  为导航网站后台管理
	http://localhost/index.php   为导航网站页面
	第一次访问后台管理时时会自动创建admin，默认密码admin123

