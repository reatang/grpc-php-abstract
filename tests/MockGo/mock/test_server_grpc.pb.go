// Code generated by protoc-gen-go-grpc. DO NOT EDIT.
// versions:
// - protoc-gen-go-grpc v1.3.0
// - protoc             v3.21.12
// source: test_server.proto

package mock

import (
	context "context"
	grpc "google.golang.org/grpc"
	codes "google.golang.org/grpc/codes"
	status "google.golang.org/grpc/status"
)

// This is a compile-time assertion to ensure that this generated file
// is compatible with the grpc package it is being compiled against.
// Requires gRPC-Go v1.32.0 or later.
const _ = grpc.SupportPackageIsVersion7

const (
	TestServer_Ping_FullMethodName = "/reatang.grpc_php_abstract.tests.mock.TestServer/Ping"
	TestServer_OTel_FullMethodName = "/reatang.grpc_php_abstract.tests.mock.TestServer/OTel"
)

// TestServerClient is the client API for TestServer service.
//
// For semantics around ctx use and closing/ending streaming RPCs, please refer to https://pkg.go.dev/google.golang.org/grpc/?tab=doc#ClientConn.NewStream.
type TestServerClient interface {
	Ping(ctx context.Context, in *PingRequest, opts ...grpc.CallOption) (*PingResponse, error)
	OTel(ctx context.Context, in *OTelRequest, opts ...grpc.CallOption) (*OTelResponse, error)
}

type testServerClient struct {
	cc grpc.ClientConnInterface
}

func NewTestServerClient(cc grpc.ClientConnInterface) TestServerClient {
	return &testServerClient{cc}
}

func (c *testServerClient) Ping(ctx context.Context, in *PingRequest, opts ...grpc.CallOption) (*PingResponse, error) {
	out := new(PingResponse)
	err := c.cc.Invoke(ctx, TestServer_Ping_FullMethodName, in, out, opts...)
	if err != nil {
		return nil, err
	}
	return out, nil
}

func (c *testServerClient) OTel(ctx context.Context, in *OTelRequest, opts ...grpc.CallOption) (*OTelResponse, error) {
	out := new(OTelResponse)
	err := c.cc.Invoke(ctx, TestServer_OTel_FullMethodName, in, out, opts...)
	if err != nil {
		return nil, err
	}
	return out, nil
}

// TestServerServer is the server API for TestServer service.
// All implementations must embed UnimplementedTestServerServer
// for forward compatibility
type TestServerServer interface {
	Ping(context.Context, *PingRequest) (*PingResponse, error)
	OTel(context.Context, *OTelRequest) (*OTelResponse, error)
	mustEmbedUnimplementedTestServerServer()
}

// UnimplementedTestServerServer must be embedded to have forward compatible implementations.
type UnimplementedTestServerServer struct {
}

func (UnimplementedTestServerServer) Ping(context.Context, *PingRequest) (*PingResponse, error) {
	return nil, status.Errorf(codes.Unimplemented, "method Ping not implemented")
}
func (UnimplementedTestServerServer) OTel(context.Context, *OTelRequest) (*OTelResponse, error) {
	return nil, status.Errorf(codes.Unimplemented, "method OTel not implemented")
}
func (UnimplementedTestServerServer) mustEmbedUnimplementedTestServerServer() {}

// UnsafeTestServerServer may be embedded to opt out of forward compatibility for this service.
// Use of this interface is not recommended, as added methods to TestServerServer will
// result in compilation errors.
type UnsafeTestServerServer interface {
	mustEmbedUnimplementedTestServerServer()
}

func RegisterTestServerServer(s grpc.ServiceRegistrar, srv TestServerServer) {
	s.RegisterService(&TestServer_ServiceDesc, srv)
}

func _TestServer_Ping_Handler(srv interface{}, ctx context.Context, dec func(interface{}) error, interceptor grpc.UnaryServerInterceptor) (interface{}, error) {
	in := new(PingRequest)
	if err := dec(in); err != nil {
		return nil, err
	}
	if interceptor == nil {
		return srv.(TestServerServer).Ping(ctx, in)
	}
	info := &grpc.UnaryServerInfo{
		Server:     srv,
		FullMethod: TestServer_Ping_FullMethodName,
	}
	handler := func(ctx context.Context, req interface{}) (interface{}, error) {
		return srv.(TestServerServer).Ping(ctx, req.(*PingRequest))
	}
	return interceptor(ctx, in, info, handler)
}

func _TestServer_OTel_Handler(srv interface{}, ctx context.Context, dec func(interface{}) error, interceptor grpc.UnaryServerInterceptor) (interface{}, error) {
	in := new(OTelRequest)
	if err := dec(in); err != nil {
		return nil, err
	}
	if interceptor == nil {
		return srv.(TestServerServer).OTel(ctx, in)
	}
	info := &grpc.UnaryServerInfo{
		Server:     srv,
		FullMethod: TestServer_OTel_FullMethodName,
	}
	handler := func(ctx context.Context, req interface{}) (interface{}, error) {
		return srv.(TestServerServer).OTel(ctx, req.(*OTelRequest))
	}
	return interceptor(ctx, in, info, handler)
}

// TestServer_ServiceDesc is the grpc.ServiceDesc for TestServer service.
// It's only intended for direct use with grpc.RegisterService,
// and not to be introspected or modified (even as a copy)
var TestServer_ServiceDesc = grpc.ServiceDesc{
	ServiceName: "reatang.grpc_php_abstract.tests.mock.TestServer",
	HandlerType: (*TestServerServer)(nil),
	Methods: []grpc.MethodDesc{
		{
			MethodName: "Ping",
			Handler:    _TestServer_Ping_Handler,
		},
		{
			MethodName: "OTel",
			Handler:    _TestServer_OTel_Handler,
		},
	},
	Streams:  []grpc.StreamDesc{},
	Metadata: "test_server.proto",
}
