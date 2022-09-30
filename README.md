# php-gen
基于hyperf封装的devtool组件，支持构建Controller、Dao、Entity、Model、Enum、Service、Validate代码，查看Route列表

#### 安装
composer require dengpju/php-gen

#### 使用

###### 生成[project_name]\config\autoload\gen.php配置
php bin/hyperf.php vendor:publish dengpju/php-gen

php bin/hyperf.php 

dengpju
  dengpju:controller  Build Controller.   php bin/hyperf.php dengpju:controller name=test path="Test"
  dengpju:dao         Build Dao.          php bin/hyperf.php dengpju:dao conn=default model=all Or php bin/hyperf.php dengpju:dao conn=default model=ModelName
  dengpju:entity      Build Entity.       php bin/hyperf.php dengpju:entity conn=default model=all Or php bin/hyperf.php dengpju:entity conn=default model=ModelName
  dengpju:enum        Build Enum.         php bin/hyperf.php dengpju:enum conn=default name=yes_or_no flag='是否:yes-1-是,no-2-否'
  dengpju:model       Build Model.        php bin/hyperf.php dengpju:model conn=default table=all Or php bin/hyperf.php dengpju:model conn=default table=TableName
  dengpju:route       Look Route List.    php bin/hyperf.php dengpju:route server=http
  dengpju:service     Build Service.      php bin/hyperf.php dengpju:service name=name path=path
  dengpju:validate    Build Validate.     php bin/hyperf.php dengpju:validate name=name path=path

###### 构建Controller
php bin/hyperf.php dengpju:controller name=test path="Test"

###### 构建Dao
php bin/hyperf.php dengpju:dao conn=default model=all 
Or 
php bin/hyperf.php dengpju:dao conn=default model=ModelName

###### 构建Entity
php bin/hyperf.php dengpju:entity conn=default model=all 
Or 
php bin/hyperf.php dengpju:entity conn=default model=ModelName

###### 构建Model
php bin/hyperf.php dengpju:model conn=default table=all 
Or 
php bin/hyperf.php dengpju:model conn=default table=TableName

###### 构建Service
php bin/hyperf.php dengpju:service name=name path=path

###### 构建Validate
php bin/hyperf.php dengpju:validate name=name path=path

###### 构建Enum
php bin/hyperf.php dengpju:enum conn=default name=yes_or_no flag='是否:yes-1-是,no-2-否'

###### 查看Route列表
php bin/hyperf.php dengpju:route server=http

