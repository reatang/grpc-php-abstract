package main

import (
	"context"
	"log"
	"mock_service/mock"
	"net"
	"net/http"

	"github.com/grpc-ecosystem/grpc-gateway/v2/runtime"
	"go.opentelemetry.io/contrib/instrumentation/google.golang.org/grpc/otelgrpc"
	"google.golang.org/grpc"
	"google.golang.org/grpc/credentials/insecure"
	"google.golang.org/grpc/reflection"
	"google.golang.org/grpc/test/bufconn"
)

// MockServerContextDialer 内嵌式server dialer
func MockServerContextDialer() func(context.Context, string) (net.Conn, error) {
	listener := bufconn.Listen(1024 * 1024)
	server := grpc.NewServer(grpc.UnaryInterceptor(otelgrpc.UnaryServerInterceptor()))
	mock.RegisterTestServerServer(server, &Server{})

	reflection.Register(server)
	go func() {
		if err := server.Serve(listener); err != nil {
			log.Fatal(err)
		}
	}()

	return func(context.Context, string) (net.Conn, error) {
		return listener.Dial()
	}
}

func main() {
	otelStart()

	conn, err := grpc.Dial("",
		grpc.WithContextDialer(MockServerContextDialer()),
		grpc.WithTransportCredentials(insecure.NewCredentials()),
	)
	if err != nil {
		log.Fatal(err)
	}

	mux := runtime.NewServeMux()

	err = mock.RegisterTestServerHandler(context.Background(), mux, conn)
	if err != nil {
		log.Fatal(err)
		return
	}

	http.Handle("/", mux)

	log.Println("server start :9098")
	if err := http.ListenAndServe(":9098", nil); err != nil {
		log.Fatal(err)
	}

	log.Println("server stop")
}
