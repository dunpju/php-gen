# php-gen
基于hyperf封装的devtool组件，支持构建Controller、Dao、Entity、Model、Enum、Service、Validate代码，查看Route列表

#### 安装
```
composer require dengpju/php-gen
```

#### 使用

###### 生成[project_name]\config\autoload\gen.php配置
```
php bin/hyperf.php vendor:publish dengpju/php-gen
```


###### 查看指令
```
php bin/hyperf.php 

 dengpju
  dengpju:code        Build Code.             php bin/hyperf.php dengpju:code
  dengpju:config      Build Config Instance.  php bin/hyperf.php dengpju:config
  dengpju:controller  Build Controller.       php bin/hyperf.php dengpju:controller name=test path="Test"
  dengpju:dao         Build Dao.              php bin/hyperf.php dengpju:dao model=all --conn=default --modelPath=Default Or php bin/hyperf.php dengpju:dao model=ModelName --conn=default --modelPath=Default
  dengpju:entity      Build Entity.           php bin/hyperf.php dengpju:entity model=all --conn=default --modelPath=Default Or php bin/hyperf.php dengpju:entity model=ModelName --conn=default --modelPath=Default
  dengpju:enum        Build Enum.             php bin/hyperf.php dengpju:enum conn=default name=yes_or_no flag='是否:yes-1-是,no-2-否'
  dengpju:model       Build Model.            php bin/hyperf.php dengpju:model table=all --conn=default --prefix=fm_ --path=Default Or php bin/hyperf.php dengpju:model table=table-name --conn=default --prefix=fm_ --path=Default
  dengpju:route       Look Route List.        php bin/hyperf.php dengpju:route server=http
  dengpju:service     Build Service.          php bin/hyperf.php dengpju:service name=name path=path
  dengpju:validate    Build Validate.         php bin/hyperf.php dengpju:validate name=name path=path
```

###### 构建Controller
```
php bin/hyperf.php dengpju:controller name=test path="Test"
```

###### 构建Dao
```
php bin/hyperf.php dengpju:dao model=all --conn=default --modelPath=Default
```
Or 
```
php bin/hyperf.php dengpju:dao model=ModelName --conn=default --modelPath=Default
```

###### 构建Entity
```
php bin/hyperf.php dengpju:entity model=all --conn=default --modelPath=Default
```
Or 
```
php bin/hyperf.php dengpju:entity model=ModelName --conn=default --modelPath=Default
```

###### 构建Model
```
php bin/hyperf.php dengpju:model table=all --conn=default --prefix=fm_ --path=Default
```
Or 
```
php bin/hyperf.php dengpju:model table=table-name --conn=default --prefix=fm_ --path=Default
```

###### 构建Service
```
php bin/hyperf.php dengpju:service name=name path=path
```

###### 构建Validate
```
php bin/hyperf.php dengpju:validate name=name path=path
```

###### 构建Enum
```
php bin/hyperf.php dengpju:enum conn=default name=yes_or_no flag='是否:yes-1-是,no-2-否'
```

###### 构建Code
```
php bin/hyperf.php dengpju:code
```

###### 查看Route列表
```
php bin/hyperf.php dengpju:route server=http
```

