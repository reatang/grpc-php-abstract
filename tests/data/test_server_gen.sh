#!/bin/bash

GOOGLE_IDL=${HOME}/protoc/googleapis
PROTOBUF_IDL=${HOME}/protoc/protoc-21.12/include

# PHP
protoc -I $PROTOBUF_IDL -I $GOOGLE_IDL -I ./ \
  --php_out=../ \
  --php-abs-grpc_out=../ --plugin=protoc-gen-grpc=${GOPATH}/bin/protoc-gen-php-abs-grpc \
  --grpc_out=../ --grpc_opt=generate_server --plugin=protoc-gen-grpc=${HOME}/protoc/grpc-1.52.1/grpc_php_plugin \
   test_server.proto

if [ $? -eq 0 ]; then
  cp -r ../Reatang/GrpcPHPAbstract/Tests/Mock/ ../Mock/
  rm -rf ../Reatang
fi

# go
protoc -I $PROTOBUF_IDL -I $GOOGLE_IDL -I ./ \
  --go_out=../MockGo/mock --go_opt=paths=source_relative \
  --go-grpc_out=../MockGo/mock --go-grpc_opt=paths=source_relative \
  --grpc-gateway_out=../MockGo/mock --grpc-gateway_opt=paths=source_relative \
  test_server.proto