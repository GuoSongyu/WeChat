# [](#header-1)微信第三方类库

基于微信开房文档编写的第三方类库，封装了一些常用的方法，暂时包括：

1.微信基本参数获取（包括access_token、jsapi_ticket等接口必须参数）

2.jssdk参数获取（包括signPackage中的nonceStr、signature、rawString等）

3.上传素材（临时素材和永久素材）

4.创建和删除自定义菜单

5.被动消息回复（目前只完成关键字）、发送消息（客服消息接口），但是发送消息必须保证用户与微信平台48小时之内用交互才可以，具体交互看见注释

6.用户Auth认证，获取用户基本信息



微信appid和appsecret等储存在conf文件中，获取到的access_token和jsspi_ticket也写在文件中，过期之后会重新并重新写入文件中