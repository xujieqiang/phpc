import uuid
import socket

def get_mac_address(): 
    host_name=socket.gethostname()
    ip=socket.gethostbyname(host_name)
    mac=uuid.UUID(int = uuid.getnode()).hex[-12:] 
    str=host_name+ip+ ":".join([mac[e:e+2] for e in range(0,11,2)])
    return str

if __name__=='__main__':
    get_mac_address

