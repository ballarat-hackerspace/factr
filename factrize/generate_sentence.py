import random
import dateutil.parser as dup
from datetime import datetime

awesome_sentence_generation_not_working = True  # Deadlines!

reply_forms_notime = []

reply_forms_notime.append("The {category} near you has a {attribute} value of {value}.")
reply_forms_notime.append("{value} {attribute}!? That's right, take a look at that {category} near you.")
reply_forms_notime.append("Nearby, you'll see a {category} with {value} {attribute}.")
reply_forms_notime.append("Did you know that the nearest {category} has an {attribute} value of {value}.")
reply_forms_notime.append("Did you know that the nearest {category} has an {value} {attribute}s?")
reply_forms_notime.append("There's a {singular} just {distance} metres from you. {quip}")

reply_forms_time = []
reply_forms_time.append("In the last {time_period} days, the {value_type} of {category} is {value}. {quip}")
reply_forms_time.append("Did you know that since {time_period}, the {category} near you has a {attribute} value of {value}.")
reply_forms_time.append("On {time_period}, the {category} near you has a {attribute} value of {value}.")
reply_forms_time.append("Did you know that on {time_period}, the {category} near you has a {attribute} value of {value}.")
reply_forms_time.append("There's a {singular} just {distance} metres from you. {quip}")

reply_forms_avg = []
reply_forms_avg.append("The average {category} in this area is $value. {quip}")
reply_forms_avg.append("There's a {singular} just {distance} metres from you. {quip}")

reply_forms_sum = []
#reply_forms_sum.append("In total {category} in this area is $value. {quip}")
reply_forms_sum.append("Over the last {time_period} days, nearby {plural} has totalled {value}. {quip}")
reply_forms_sum.append("There's a {singular} just {distance} metres from you. {quip}")

def generate_sentence(input_data):

    category = input_data['category']
    plural = input_data['category']['plural']
    singular = input_data['category']['singular']
    # time_period = input_data.get('datetime', None)
    time_period = input_data['time_period']
    quip = input_data['quip']
    attribute = input_data['value_name']
    data_type = input_data['type']
    try:
        distance = round(float(input_data['distance']),0)
    except TypeError:
        distance = ""
    
    value = input_data['value']

    # IBM Watson (or similar) goes here, but the training couldn't be done to a reliable level in time.
    reply_options = None
    if awesome_sentence_generation_not_working:
        if "avg" in data_type:
            reply_options = reply_forms_avg
        # Choose either a temporal or non-temporal type
        elif "sum" in data_type:
            reply_options = reply_forms_sum
        elif "datetime" in input_data and input_data['datetime']:
            reply_options = reply_forms_time
            # Format datetime to be human readable
            dt = None
            if isinstance(time_period, int):
                dt = datetime.fromtimestamp(time_period)
            elif not isinstance(time_period, datetime):
                dt = dup.parse(time_period)
            time_period = dt.strftime("%A %d %M, %Y")
        else:
            reply_options = reply_forms_notime

    # Choose a reply form at random and populate it
    return random.choice(reply_options).format(category=category, attribute=attribute,
                                               value=value, time_period=time_period, plural=plural,
                                               distance=distance, singular=singular, quip=quip)