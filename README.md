# grpc-php-abstract

abstract grpc and grpc-gateway

- [x] 提供grpc原生客户端 和 grcp-gateway调用的抽象层
- [x] 添加解析 protobuf Any 参数的工具 UtilAny
- [x] 一元请求重试中间件
- [x] 支持配置化的默认调用行为
- [ ] 支持链路追踪的metadata传输
- [x] 开发自动生成grpc原生客户端抽象层 `https://github.com/reatang/protoc-gen-php-abs-grpc`
- [ ] 开发自动生成grpc-gateway抽象层 `很显然，没搞呢`

## 注意

- 2023-07-06：protobuf 3.23 是支持 php7.4的最后一个版本，使用php7.4的同学`不要`再用更新的版本了，[相关新闻](https://protobuf.dev/news/2023-07-06/)