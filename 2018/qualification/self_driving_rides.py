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

class Data(object):
    """Data with its statistics"""
    def __init__(self):
        self.min = 1000000 # todo: improvable
        self.max = 0
        self.total = 0
        self.hit = 0

    def get_statistics(self):
        return ('%d (%d < %d < %d)' % (self.total, self.min, int(self.total / self.hit), self.max))

    def update_statistics(self, value):
        if self.min > value:
            self.min = value
        if self.max < value:
            self.max = value
        self.total += value
        self.hit += 1
        
class Statistics(object):
    """Rides Statistics"""
    def __init__(self):
        self.max_total_bonus = 0
        self.max_total_steps = 0
        self.total_bonus = 0
        self.total_score = 0
        self.steps = Data()
        self.arrive_steps = Data()
        self.lost_steps = Data()

    def print_statistics(self):
        print('Score: %d / %d (of which steps: %d / %d, and bonus: %d / %d)' % (self.total_score, (self.steps.total + self.max_total_bonus), (self.total_score - self.total_bonus), self.steps.total, self.total_bonus, self.max_total_bonus))
        print('Ride steps: %s' % self.steps.get_statistics())
        print('Total arrive steps: %s)' % self.arrive_steps.get_statistics())
        print('Total lost steps: %s' % self.lost_steps.get_statistics())

    def update_statistics(self, score, bonus, arrive, lost_steps):
        self.total_score += score
        self.total_bonus += bonus
        self.arrive_steps.update_statistics(arrive)
        self.lost_steps.update_statistics(lost_steps)

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
        self.rs = Statistics()
        
    def get_ride_statistics(self):
        vehicle = self.v
        self.rs.steps.min = self.rides[0].steps
        self.rs.steps.max = self.rides[0].steps
        for r in range(0, self.r):
            # max_total_bonus
            if self.rides[r].e == 0 and self.rides[r].x == 0 and self.rides[r].y == 0 and vehicle > 0:
                vehicle -= 1
                self.rs.max_total_bonus += self.b
            if self.rides[r].e > 0:
                self.rs.max_total_bonus += self.b
            # rides steps: min, max and todo
            if self.rs.steps.min > self.rides[r].steps:
                self.rs.steps.min = self.rides[r].steps
            if self.rs.steps.max < self.rides[r].steps:
                self.rs.steps.max = self.rides[r].steps
            self.rs.steps.total += self.rides[r].steps
            self.rs.steps.hit += 1

    def get_ride_index(self, step, v, rides):
        max_weight = 0
        score = 0
        bonus = 0
        arrive = 0
        lost = 0
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
                score = total_score
                bonus = bonus_score
                arrive = a_step
                lost = lost_steps
                index = i
        self.rs.update_statistics(score, bonus, arrive, lost)
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
                else:
                    print('Rides have been finished and it is step number %d / %d' % (t, self.s))
                    return
        print('How many rides are to be completed? %d / %d' % (len(rides_todo), self.r))

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

    # create statistics
    resource.get_ride_statistics()

    # self driving rides
    resource.drive()

    # print statistics
    resource.rs.print_statistics()

    # write output file
    write_output(resource, sys.argv[2])

if __name__ == '__main__':
    main()
