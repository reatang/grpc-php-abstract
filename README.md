# grpc-php-abstract

abstract grpc and grpc-gateway

- [x] 提供grpc原生客户端 和 grcp-gateway调用的抽象层
- [x] 添加解析 protobuf Any 参数的工具 UtilAny
- [x] 一元请求重试中间件
- [x] 支持配置化的默认调用行为
- [x] 支持OTLP链路追踪
- [x] 开发自动生成grpc原生客户端抽象层 `https://github.com/reatang/protoc-gen-php-abs-grpc`
- [ ] 开发自动生成grpc-gateway抽象层 `很显然，没搞呢`

## 业务错误信息传递方案

在golang的grpc业务处理中，一般返回值是两个：
```go
return response, error
```
这第二个error参数如果不是标准的grpc错误码，则对应这grpc的status则是：`code:2, details: error的内容`。
那么我就可以利用这个error的内容做一个简单的数据格式用来携带错误。
我的details内容格式则是：
```text
!int:string
!错误码:错误信息string
如：
!100:您的操作有误
```
按照这种格式传输的details，则会自动抛出 `GrpcBusinessException` 异常。

## 注意

- 2023-07-06：protobuf 3.23 是支持 php7.4的最后一个版本，使用php7.4的同学`不要`再用更新的版本了，[相关新闻](https://protobuf.dev/news/2023-07-06/)

## 文档列表

- PHP grpc 使用文档：https://grpc.io/docs/languages/php
- PHP grpc 源码文档：https://github.com/grpc/grpc/tree/master/src/php