package main

import (
	"mock_service/mock"
	"net"

	"go.opentelemetry.io/contrib/instrumentation/google.golang.org/grpc/otelgrpc"
	"google.golang.org/grpc"
	"google.golang.org/grpc/reflection"
)

func main() {
	otelStart()

	// 服务启动
	lis, err := net.Listen("tcp", ":9099")
	if err != nil {
		panic(err)
	}

	options := []grpc.ServerOption{
		grpc.UnaryInterceptor(otelgrpc.UnaryServerInterceptor()),
	}

	server := grpc.NewServer(options...)

	mock.RegisterTestServerServer(server, &Server{})
	reflection.Register(server)

	if err := server.Serve(lis); err != nil {
		panic(err)
	}
}
