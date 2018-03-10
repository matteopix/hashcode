import sys

class Vehicle(object):
    """Vehicle"""
    def __init__(self):
        self.x = 0
        self.y = 0
        self.ride = None
        self.last_step = -1
        self.ride_step = 0
        self.a_step = 0
        self.w_step = 0
        self.rides = []

    def is_busy(self, step):
        if step <= self.last_step:
            return 1
        return 0

    def add_ride(self, step, ride):
        self.ride_step = ride.steps
        self.a_step = abs(self.x - ride.a) + abs(self.y - ride.b)
        self.w_step = step + self.a_step
        if self.w_step <= ride.e:
            self.w_step = ride.e
        self.last_step = self.w_step + self.ride_step
        self.x = ride.x
        self.y = ride.y
        self.rides.append(ride.i)

class Ride(object):
    """Ride"""
    def __init__(self, a, b, x, y, e, l):
        self.a = a
        self.b = b
        self.x = x
        self.y = y
        self.e = e
        self.l = l
        self.steps = abs(x - a) + abs(y - b)
        self.i = 0

class Resources(object):
    """Resources"""
    def __init__(self, rows, cols, v, r, b, s):
        self.rows = rows
        self.cols = cols
        self.v = v
        self.r = r
        self.b = b
        self.s = s
        self.rides = []
        self.vehicles = []

    def get_ride_index(self, step, v, rides):
        max_weight = 0
        index = 0 # if no ride is assigned
        c = len(rides)
        for i in range(0, c):
            r = rides[i]
            ride_score = r.steps
            a_step = abs(v.x - r.a) + abs(v.y - r.b)
            w_step = step + a_step
            bonus_score = 0
            if w_step <= r.e:
                bonus_score = self.b
                w_step = r.e
            if r.l < w_step + ride_score:
                ride_score = 0
            total_score = bonus_score + ride_score
            lost_steps = r.e - step - a_step
            weight = self.s - a_step
            if lost_steps > 0 and r.e > 0:
                weight -= lost_steps
            if bonus_score > 0:
                weight += bonus_score
            if weight > max_weight:
                max_weight = weight
                index = i
        return index
    
    def drive(self):
        rides_todo = []
        for v in range(0, self.v):
            self.vehicles.append(Vehicle())
        for r in range(0, self.r):
            rides_todo.append(self.rides[r])
        for t in range(0, self.s):
            for v in range(0, self.v):
                if len(rides_todo) > 0:
                    if self.vehicles[v].is_busy(t) == 0:
                        ride_index = self.get_ride_index(t, self.vehicles[v], rides_todo)
                        self.vehicles[v].add_ride(t, rides_todo.pop(ride_index))

def read_input(filename):
    """Read the input file."""
    with open(filename, 'r') as fin:
        resource = Resources(*[int(num) for num in fin.readline().split()])
        # read matrix
        for i in range(resource.r):
            line = fin.readline().strip()
            resource.rides.append(Ride(*[int(num) for num in line.split()]))
            resource.rides[i].i = i
    return resource

def write_output(resource, filename):
    """Write the output file."""
    print('Output file: %s' % filename)
    with open(filename, 'w') as fout:
        for v in range(0, resource.v):
            fout.write('%d %s\n' % (len(resource.vehicles[v].rides), ' '.join([str(x) for x in resource.vehicles[v].rides])))

def main():
    """Main function."""

    if len(sys.argv) < 3:
        sys.exit('Syntax: %s <input> <output>' % sys.argv[0])

    # read data and initialize the matrix
    resource = read_input(sys.argv[1])

    # self driving rides
    resource.drive()


    # write output file
    write_output(resource, sys.argv[2])

if __name__ == '__main__':
    main()
