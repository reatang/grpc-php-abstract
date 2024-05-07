package main

import (
	"context"
	"fmt"
	"mock_service/mock"
	"os"

	"go.opentelemetry.io/otel"
	"go.opentelemetry.io/otel/baggage"
	"go.opentelemetry.io/otel/exporters/stdout/stdouttrace"
	"go.opentelemetry.io/otel/propagation"
	"go.opentelemetry.io/otel/sdk/resource"
	sdktrace "go.opentelemetry.io/otel/sdk/trace"
	semconv "go.opentelemetry.io/otel/semconv/v1.17.0"
	oteltrace "go.opentelemetry.io/otel/trace"
	"google.golang.org/grpc/codes"
	"google.golang.org/grpc/metadata"
	"google.golang.org/grpc/status"
)

type Server struct {
	mock.UnimplementedTestServerServer
}

func (s Server) Ping(ctx context.Context, request *mock.PingRequest) (*mock.PingResponse, error) {
	if request.GetPing() == "" || request.GetPing() == "ping" || request.GetPing() == "test" {
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

func (s Server) OTel(ctx context.Context, request *mock.OTelRequest) (*mock.OTelResponse, error) {
	bag, span := baggage.FromContext(ctx), oteltrace.SpanContextFromContext(ctx)

	return &mock.OTelResponse{
		Trace:   span.TraceID().String(),
		Baggage: bag.Member("baggage1").Value(),
	}, nil
}

func otelStart() {
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
}
