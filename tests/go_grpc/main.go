package main

import (
	"context"
	"fmt"
	"go_mock/mock"
	"net"
	"os"

	"go.opentelemetry.io/contrib/instrumentation/google.golang.org/grpc/otelgrpc"
	"go.opentelemetry.io/otel"
	"go.opentelemetry.io/otel/baggage"
	"go.opentelemetry.io/otel/exporters/stdout/stdouttrace"
	"go.opentelemetry.io/otel/propagation"
	"go.opentelemetry.io/otel/sdk/resource"
	sdktrace "go.opentelemetry.io/otel/sdk/trace"
	semconv "go.opentelemetry.io/otel/semconv/v1.17.0"
	oteltrace "go.opentelemetry.io/otel/trace"
	"google.golang.org/grpc"
	"google.golang.org/grpc/codes"
	"google.golang.org/grpc/metadata"
	"google.golang.org/grpc/reflection"
	"google.golang.org/grpc/status"
)

type Service struct {
	mock.UnimplementedTestServerServer
}

func (s Service) Ping(ctx context.Context, request *mock.PingRequest) (*mock.PingResponse, error) {
	if request.GetPing() == "" || request.GetPing() == "ping" {
		return &mock.PingResponse{Pong: "PONG"}, nil
	} else if request.GetPing() == "metadata" {
		if md, b := metadata.FromIncomingContext(ctx); b {
			abc := md.Get("abc")
			if len(abc) == 0 {
				return nil, status.Errorf(codes.InvalidArgument, "参数错误")
			}

			return &mock.PingResponse{Pong: fmt.Sprintf("PONG%s", abc[0])}, nil
		}
	}

	return nil, status.Errorf(codes.NotFound, "未找到的测试")
}

func (s Service) OTel(ctx context.Context, request *mock.OTelRequest) (*mock.OTelResponse, error) {
	bag, span := baggage.FromContext(ctx), oteltrace.SpanContextFromContext(ctx)

	fmt.Printf("%s, %s\n", span.TraceID().String(), bag.Member("baggage1").Value())
	return &mock.OTelResponse{
		Trace:   span.TraceID().String(),
		Baggage: bag.Member("baggage1").Value(),
	}, nil
}

func main() {
	// otel 初始化
	resourceAttr, err := resource.New(context.Background(),
		resource.WithAttributes(
			semconv.ServiceName("go_grpc_mock"),
		),
	)
	if err != nil {
		panic(err)
	}

	exporter, err := stdouttrace.New(stdouttrace.WithWriter(os.Stdout))
	if err != nil {
		panic(err)
	}

	tp := sdktrace.NewTracerProvider(
		sdktrace.WithSampler(sdktrace.ParentBased(sdktrace.TraceIDRatioBased(1.0))),
		sdktrace.WithResource(resourceAttr),
		sdktrace.WithBatcher(exporter),
	)
	otel.SetTracerProvider(tp)
	otel.SetTextMapPropagator(propagation.NewCompositeTextMapPropagator(propagation.TraceContext{}, propagation.Baggage{}))

	// 服务启动
	lis, err := net.Listen("tcp", ":9099")
	if err != nil {
		panic(err)
	}

	options := []grpc.ServerOption{
		grpc.UnaryInterceptor(otelgrpc.UnaryServerInterceptor()),
	}

	server := grpc.NewServer(options...)

	mock.RegisterTestServerServer(server, &Service{})
	reflection.Register(server)

	if err := server.Serve(lis); err != nil {
		panic(err)
	}
}
