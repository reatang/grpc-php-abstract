#!/bin/bash

# PHP
protoc -I ./ \
  --php_out=../ \
  --php-abs-grpc_out=../ --plugin=protoc-gen-grpc=${GOPATH}/bin/protoc-gen-php-abs-grpc \
  --grpc_out=../ --grpc_opt=generate_server --plugin=protoc-gen-grpc=${HOME}/protoc/grpc-1.52.1/grpc_php_plugin \
   test_server.proto

if [ $? -eq 0 ]; then
  cp -r ../Reatang/GrpcPHPAbstract/Tests/Mock/ ../Mock/
  rm -rf ../Reatang
fi

# go
protoc -I ./ \
  --go_out=../go_grpc/mock --go_opt=paths=source_relative \
  --go-grpc_out=../go_grpc/mock --go-grpc_opt=paths=source_relative \
  test_server.proto